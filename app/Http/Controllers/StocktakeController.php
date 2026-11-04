<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Stocktake;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class StocktakeController extends Controller
{
    public function index(): View
    {
        return view('stocktakes.index', ['stocktakes' => Stocktake::with(['product', 'user'])->latest()->paginate(25)]);
    }

    public function create(): View
    {
        return view('stocktakes.create', ['products' => Product::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request, DatabaseManager $database): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'counted_quantity' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        try {
            $database->transaction(function () use ($data, $request): void {
                $balance = StockBalance::query()->lockForUpdate()->firstOrCreate(['product_id' => $data['product_id']]);
                $expected = (float) $balance->physical_quantity;
                $counted = (float) $data['counted_quantity'];
                $difference = number_format($counted - $expected, 3, '.', '');
                $stocktake = Stocktake::create([
                    'product_id' => $data['product_id'],
                    'user_id' => $request->user()->id,
                    'expected_quantity' => number_format($expected, 3, '.', ''),
                    'counted_quantity' => number_format($counted, 3, '.', ''),
                    'difference_quantity' => $difference,
                    'reason' => $data['reason'],
                ]);
                $balance->update(['physical_quantity' => number_format($counted, 3, '.', '')]);
                if ((float) $difference !== 0.0) {
                    StockMovement::create([
                        'product_id' => $data['product_id'],
                        'user_id' => $request->user()->id,
                        'quantity' => $difference,
                        'type' => 'adjustment',
                        'reason' => "Stocktake #{$stocktake->id}: {$data['reason']}",
                    ]);
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['stocktake' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Stocktake gagal disimpan.'])->withInput();
        }

        return redirect()->route('stocktakes.index')->with('success', 'Stocktake berhasil disimpan.');
    }
}
