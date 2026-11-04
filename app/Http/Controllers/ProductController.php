<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
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
        return view('products.create', ['categories' => Category::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100', 'unique:products,code'],
            'unit' => ['required', 'string', 'max:50'],
            'store_price' => ['required', 'numeric', 'min:0'],
            'hotel_price' => ['required', 'numeric', 'min:0'],
        ]);
        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil dibuat.');
    }
}
