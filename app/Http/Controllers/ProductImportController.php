<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProductImportController extends Controller
{
    public function create(): View
    {
        return view('products.import');
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
        $handle = fopen($request->file('file')->getRealPath(), 'rb');
        $headers = $handle !== false ? fgetcsv($handle) : false;
        $rows = [];

        if ($handle === false || $headers === false) {
            return back()->withErrors(['file' => 'File CSV tidak dapat dibaca.'])->withInput();
        }

        $headers = array_map(static fn (mixed $header): string => trim((string) $header), $headers);
        $required = ['name', 'code', 'unit', 'store_price', 'hotel_price'];
        if (array_diff($required, $headers) !== []) {
            fclose($handle);

            return back()->withErrors(['file' => 'Header CSV wajib: name, code, unit, store_price, hotel_price.'])->withInput();
        }

        while (($values = fgetcsv($handle)) !== false) {
            if (count(array_filter($values, static fn (mixed $value): bool => trim((string) $value) !== '')) === 0) {
                continue;
            }
            $row = array_combine($headers, array_pad($values, count($headers), null));
            $validator = Validator::make($row, [
                'name' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:100'],
                'unit' => ['required', 'string', 'max:50'],
                'store_price' => ['required', 'numeric', 'min:0'],
                'hotel_price' => ['required', 'numeric', 'min:0'],
            ]);
            $rows[] = ['data' => $row, 'errors' => $validator->errors()->all()];
        }
        fclose($handle);

        if ($rows === []) {
            return back()->withErrors(['file' => 'File CSV tidak memiliki data produk.'])->withInput();
        }

        $request->session()->put('product_import_rows', $rows);

        return view('products.import', compact('rows'));
    }

    public function store(Request $request): RedirectResponse
    {
        $rows = $request->session()->pull('product_import_rows', []);
        $errors = collect($rows)->flatMap(fn (array $row): array => $row['errors'])->values()->all();

        if ($rows === [] || $errors !== []) {
            return redirect()->route('products.import.create')->withErrors(['file' => $errors[0] ?? 'Preview import sudah kedaluwarsa. Upload ulang file CSV.']);
        }

        foreach ($rows as $row) {
            Product::updateOrCreate(['code' => $row['data']['code']], $row['data']);
        }

        return redirect()->route('products.index')->with('success', count($rows).' produk berhasil diimport.');
    }
}
