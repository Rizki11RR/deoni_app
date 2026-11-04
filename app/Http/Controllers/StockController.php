<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class StockController extends Controller
{
    public function index(): View
    {
        return view('stock.index', ['balances' => StockBalance::with('product')->latest()->get()]);
    }

    public function create(): View
    {
        return view('stock.create', ['products' => Product::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request, DatabaseManager $database): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'type' => ['required', 'in:in,out,adjustment'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        try {
            $database->transaction(function () use ($data, $request): void {
                $balance = StockBalance::query()->lockForUpdate()->firstOrCreate(['product_id' => $data['product_id']]);
                $quantity = (string) $data['quantity'];
                $physical = (string) $balance->physical_quantity;
                $newPhysical = number_format($data['type'] === 'in' ? (float) $physical + (float) $quantity : (float) $physical - (float) $quantity, 3, '.', '');

                if ((float) $newPhysical < 0) {
                    throw new \InvalidArgumentException('Stok fisik tidak boleh kurang dari nol.');
                }

                $balance->update(['physical_quantity' => $newPhysical]);
                StockMovement::create([
                    'product_id' => $data['product_id'],
                    'user_id' => $request->user()->id,
                    'quantity' => $data['type'] === 'in' ? $data['quantity'] : number_format(-1 * (float) $data['quantity'], 3, '.', ''),
                    'type' => $data['type'],
                    'reason' => $data['reason'],
                ]);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['stock' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Perubahan stok gagal disimpan.'])->withInput();
        }

        return redirect()->route('stock.index')->with('success', 'Perubahan stok berhasil disimpan.');
    }
}
