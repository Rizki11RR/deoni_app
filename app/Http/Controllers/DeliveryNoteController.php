<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\DocumentSequence;
use App\Models\Order;
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

    public function deliver(DeliveryNote $deliveryNote): RedirectResponse
    {
        if ($deliveryNote->status !== 'issued') {
            return back()->withErrors(['delivery_note' => 'Surat jalan sudah diproses.']);
        }

        $deliveryNote->update(['status' => 'delivered', 'delivered_at' => now()]);

        return back()->with('success', "Surat jalan {$deliveryNote->document_number} berhasil diselesaikan.");
    }
}
