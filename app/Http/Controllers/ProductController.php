<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Uom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.index', ['products' => Product::with('category')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('products.create', ['categories' => Category::where('is_active', true)->orderBy('name')->get(), 'uoms' => Uom::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'uom_id' => ['required', 'exists:uoms,id'],
            'purchase_uom_id' => ['nullable', 'exists:uoms,id'],
            'sales_uom_id' => ['nullable', 'exists:uoms,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100', 'unique:products,code'],
            'unit' => ['required', 'string', 'max:50'],
            'store_price' => ['required', 'numeric', 'min:0'],
            'hotel_price' => ['required', 'numeric', 'min:0'],
        ]);
        $uoms = Uom::with('category')->whereIn('id', collect([$data['uom_id'], $data['purchase_uom_id'] ?? null, $data['sales_uom_id'] ?? null])->filter())->get();
        $baseCategoryId = $uoms->firstWhere('id', $data['uom_id'])?->uom_category_id;
        if ($uoms->contains(fn (Uom $uom): bool => ! $uom->is_active || ! $uom->category?->is_active || $uom->uom_category_id !== $baseCategoryId)) {
            return back()->withErrors(['uom_id' => 'Semua satuan harus aktif dalam kategori yang sama.'])->withInput();
        }

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil dibuat.');
    }
}
