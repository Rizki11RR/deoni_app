<?php

namespace App\Http\Controllers;

use App\Models\UomCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UomCategoryController extends Controller
{
    public function index(): View
    {
        return view('uom-categories.index', ['categories' => UomCategory::withCount('uoms')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        UomCategory::create($request->validate(['name' => ['required', 'string', 'max:255', 'unique:uom_categories,name']]));

        return back()->with('success', 'Kategori satuan berhasil dibuat.');
    }
}
