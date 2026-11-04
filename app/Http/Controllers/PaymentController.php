<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    public function index(): View
    {
        return view('payments.index', ['orders' => Order::with(['customer', 'payments'])->withSum('payments', 'amount')->latest()->get()]);
    }

    public function create(Order $order): View
    {
        abort_if($order->status === 'cancelled', 422, 'Pembayaran tidak dapat dicatat untuk order yang dibatalkan.');

        $paid = $order->payments()->sum('amount');

        return view('payments.create', compact('order', 'paid'));
    }

    public function reverse(Request $request, Payment $payment, DatabaseManager $database): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:1000']]);

        try {
            $database->transaction(function () use ($payment, $request, $data): void {
                $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($lockedPayment->order_id);

                if ($lockedOrder->status === 'cancelled') {
                    throw new \InvalidArgumentException('Pembayaran order yang dibatalkan tidak dapat dibatalkan lagi.');
                }

                if ($lockedPayment->reversal_of_id !== null) {
                    throw new \InvalidArgumentException('Pembayaran sudah dibatalkan.');
                }

                if ($lockedPayment->amount <= 0) {
                    throw new \InvalidArgumentException('Nominal pembayaran tidak valid.');
                }

                Payment::create([
                    'order_id' => $lockedPayment->order_id,
                    'received_by' => $request->user()->id,
                    'amount' => -$lockedPayment->amount,
                    'method' => $lockedPayment->method,
                    'reference' => $lockedPayment->reference,
                    'notes' => $data['reason'],
                    'paid_at' => now(),
                    'reversal_of_id' => $lockedPayment->id,
                ]);
                $this->refreshPaymentStatus($lockedPayment->order_id);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['payment' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Reversal pembayaran gagal.'])->withInput();
        }

        return back()->with('success', 'Pembayaran berhasil dibatalkan.');
    }

    private function refreshPaymentStatus(int $orderId): void
    {
        $order = Order::query()->lockForUpdate()->findOrFail($orderId);
        $paid = (float) $order->payments()->sum('amount');
        $total = (float) $order->total;
        $status = $paid <= 0 ? 'unpaid' : ($paid >= $total ? 'paid' : 'partially_paid');
        $order->update(['payment_status' => $status]);
    }

    public function store(Request $request, Order $order, DatabaseManager $database): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', 'in:cash,qris,transfer,debit'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $database->transaction(function () use ($data, $order, $request): void {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

                if ($lockedOrder->status === 'cancelled') {
                    throw new \InvalidArgumentException('Pembayaran tidak dapat dicatat untuk order yang dibatalkan.');
                }

                $paid = $lockedOrder->payments()->sum('amount');
                $remaining = number_format((float) $lockedOrder->total - (float) $paid, 2, '.', '');

                if ((float) $data['amount'] > (float) $remaining) {
                    throw new \InvalidArgumentException('Jumlah pembayaran melebihi sisa tagihan.');
                }

                Payment::create([
                    'order_id' => $lockedOrder->id,
                    'received_by' => $request->user()->id,
                    'amount' => $data['amount'],
                    'method' => $data['method'],
                    'reference' => $data['reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'paid_at' => now(),
                ]);
                $this->refreshPaymentStatus($lockedOrder->id);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['payment' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Pembayaran gagal disimpan.'])->withInput();
        }

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil dicatat.');
    }
}
