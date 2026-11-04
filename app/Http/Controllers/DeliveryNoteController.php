<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\DocumentSequence;
use App\Models\Order;
use App\Models\StockBalance;
use App\Models\StockMovement;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DeliveryNoteController extends Controller
{
    public function index(): View
    {
        return view('delivery-notes.index', ['notes' => DeliveryNote::with(['order.customer', 'creator'])->latest()->paginate(25)]);
    }

    public function create(): View
    {
        return view('delivery-notes.create', ['orders' => Order::with('customer')->where('status', '!=', 'cancelled')->where('preparation_status', 'ready')->doesntHave('deliveryNote')->latest()->get()]);
    }

    public function store(Request $request, DatabaseManager $database): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'delivery_method' => ['required', 'in:pickup,delivery'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['delivery_method'] === 'delivery' && blank($data['address'] ?? null)) {
            return back()->withErrors(['address' => 'Alamat wajib diisi untuk pengiriman.'])->withInput();
        }

        try {
            $note = $database->transaction(function () use ($data, $request): DeliveryNote {
                $order = Order::query()->lockForUpdate()->findOrFail($data['order_id']);

                if ($order->status === 'cancelled' || $order->preparation_status !== 'ready') {
                    throw new \InvalidArgumentException('Order belum siap dikirim atau sudah dibatalkan.');
                }

                if ($order->deliveryNote()->exists()) {
                    throw new \InvalidArgumentException('Surat jalan untuk order ini sudah dibuat.');
                }

                $sequence = DocumentSequence::query()->lockForUpdate()->firstOrCreate(['document_type' => 'delivery'], ['last_number' => 0]);
                $sequence->increment('last_number');

                return DeliveryNote::create([
                    'order_id' => $order->id,
                    'created_by' => $request->user()->id,
                    'document_number' => 'SJ-'.str_pad((string) $sequence->fresh()->last_number, 6, '0', STR_PAD_LEFT),
                    'delivery_method' => $data['delivery_method'],
                    'status' => 'issued',
                    'address' => $data['address'] ?? null,
                ]);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['delivery_note' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Surat jalan gagal dibuat.'])->withInput();
        }

        return redirect()->route('delivery-notes.index')->with('success', "Surat jalan {$note->document_number} berhasil dibuat.");
    }

    public function deliver(DeliveryNote $deliveryNote, DatabaseManager $database, Request $request): RedirectResponse
    {
        try {
            $database->transaction(function () use ($deliveryNote, $request): void {
                $note = DeliveryNote::query()->lockForUpdate()->findOrFail($deliveryNote->id);
                if ($note->status !== 'issued') {
                    throw new \InvalidArgumentException('Surat jalan sudah diproses.');
                }

                $order = Order::query()->lockForUpdate()->with('items')->findOrFail($note->order_id);
                if ($order->status === 'cancelled' || $order->preparation_status !== 'ready') {
                    throw new \InvalidArgumentException('Order tidak dapat dikirim.');
                }

                foreach ($order->items as $item) {
                    $quantity = (float) ($item->base_quantity ?? $item->quantity);
                    $balance = StockBalance::query()->lockForUpdate()->where('product_id', $item->product_id)->first();
                    if ($balance === null || (float) $balance->physical_quantity < $quantity) {
                        throw new \InvalidArgumentException('Stok fisik tidak mencukupi.');
                    }
                    $balance->decrement('physical_quantity', $quantity);
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'user_id' => $request->user()->id,
                        'quantity' => number_format(-$quantity, 3, '.', ''),
                        'type' => 'out',
                        'reason' => "Delivery {$note->document_number}",
                        'order_id' => $order->id,
                    ]);
                }

                foreach ($order->stockReservations()->where('status', 'reserved')->lockForUpdate()->get() as $reservation) {
                    $balance = StockBalance::query()->lockForUpdate()->where('product_id', $reservation->product_id)->firstOrFail();
                    $balance->decrement('reserved_quantity', $reservation->quantity);
                    $reservation->update(['status' => 'fulfilled']);
                }
                $note->update(['status' => 'delivered', 'delivered_at' => now()]);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['delivery_note' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Surat jalan gagal diselesaikan.']);
        }

        return back()->with('success', "Surat jalan {$deliveryNote->document_number} berhasil diselesaikan.");
    }
}
