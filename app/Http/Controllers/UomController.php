<?php

namespace App\Http\Controllers;

use App\Models\Uom;
use App\Models\UomCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UomController extends Controller
{
    public function index(): View
    {
        return view('uoms.index', ['uoms' => Uom::with('category')->orderBy('name')->get(), 'categories' => UomCategory::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['uom_category_id' => ['required', 'exists:uom_categories,id'], 'name' => ['required', 'string', 'max:255'], 'symbol' => ['required', 'string', 'max:20'], 'conversion_factor' => ['required', 'numeric', 'gt:0'], 'conversion_type' => ['required', 'in:multiply,divide'], 'precision' => ['required', 'integer', 'min:0', 'max:6']]);
        $category = UomCategory::findOrFail($data['uom_category_id']);
        if (Uom::where('uom_category_id', $category->id)->where('is_base', true)->doesntExist()) {
            $data['is_base'] = true;
            $data['conversion_factor'] = 1;
        }
        Uom::create($data);

        return back()->with('success', 'Satuan berhasil dibuat.');
    }
}
