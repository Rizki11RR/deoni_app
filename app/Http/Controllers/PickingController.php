<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PickingController extends Controller
{
    public function index(): View
    {
        return view('picking.index', ['orders' => Order::with(['customer', 'items'])->where('status', '!=', 'cancelled')->whereIn('preparation_status', ['pending', 'preparing'])->latest()->paginate(25)]);
    }

    public function update(Request $request, Order $order, DatabaseManager $database): RedirectResponse
    {
        $data = $request->validate(['preparation_status' => ['required', 'in:pending,preparing,ready']]);

        try {
            $database->transaction(function () use ($data, $order): void {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

                if ($lockedOrder->status === 'cancelled') {
                    throw new \InvalidArgumentException('Order yang dibatalkan tidak dapat diproses.');
                }

                if ($lockedOrder->preparation_status === 'ready' && $data['preparation_status'] !== 'ready') {
                    throw new \InvalidArgumentException('Order siap tidak dapat dikembalikan ke status sebelumnya.');
                }

                $lockedOrder->update(['preparation_status' => $data['preparation_status']]);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['picking' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Status persiapan gagal diperbarui.']);
        }

        return back()->with('success', "Status {$order->document_number} berhasil diperbarui.");
    }
}
