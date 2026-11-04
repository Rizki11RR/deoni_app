<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Uom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductPackagingController extends Controller
{
    public function index(Product $product): View
    {
        return view('products.packagings', ['product' => $product->load('packagings.uom'), 'uoms' => Uom::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['uom_id' => ['required', 'exists:uoms,id'], 'name' => ['required', 'string', 'max:255'], 'quantity' => ['required', 'numeric', 'gt:0'], 'store_price' => ['nullable', 'numeric', 'min:0'], 'hotel_price' => ['nullable', 'numeric', 'min:0']]);
        $uom = Uom::with('category')->findOrFail($data['uom_id']);
        if (! $uom->is_active || ! $uom->category?->is_active) {
            return back()->withErrors(['uom_id' => 'Satuan harus aktif dan memiliki kategori aktif.'])->withInput();
        }
        if ($product->uom && $product->uom->uom_category_id !== $uom->uom_category_id) {
            return back()->withErrors(['uom_id' => 'Kemasan harus memakai kategori satuan produk.'])->withInput();
        }
        $product->packagings()->create($data);

        return back()->with('success', 'Kemasan berhasil dibuat.');
    }
}
