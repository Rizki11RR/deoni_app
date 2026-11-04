<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DocumentSequence;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockReservation;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('orders.index', ['orders' => Order::with(['customer', 'creator'])->latest()->get()]);
    }

    public function create(): View
    {
        return view('orders.create', [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    private function nextDocumentNumber(string $documentType): string
    {
        $sequence = DocumentSequence::query()->lockForUpdate()->firstOrCreate(
            ['document_type' => $documentType],
            ['last_number' => 0],
        );
        $sequence->increment('last_number');

        return (string) $sequence->fresh()->last_number;
    }

    public function cancel(Order $order, DatabaseManager $database): RedirectResponse
    {
        try {
            $database->transaction(function () use ($order): void {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

                if ($lockedOrder->status === 'cancelled') {
                    throw new \InvalidArgumentException('Order sudah dibatalkan.');
                }

                foreach ($lockedOrder->stockReservations()->where('status', 'reserved')->lockForUpdate()->get() as $reservation) {
                    $balance = StockBalance::query()->lockForUpdate()->where('product_id', $reservation->product_id)->first();

                    if ($balance === null || (float) $balance->reserved_quantity < (float) $reservation->quantity) {
                        throw new \RuntimeException('Saldo reservasi stok tidak konsisten.');
                    }

                    $balance->update(['reserved_quantity' => number_format((float) $balance->reserved_quantity - (float) $reservation->quantity, 3, '.', '')]);
                    $reservation->update(['status' => 'cancelled']);
                }

                $lockedOrder->update(['status' => 'cancelled']);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['order' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Order gagal dibatalkan.'])->withInput();
        }

        return back()->with('success', "Order {$order->document_number} berhasil dibatalkan.");
    }

    public function store(Request $request, DatabaseManager $database): RedirectResponse
    {
        $data = $request->validate([
            'document_type' => ['required', 'in:store,hotel,sales,pos'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'discount_type' => ['nullable', 'in:amount,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($data['document_type'] !== 'pos' && empty($data['customer_id'])) {
            return back()->withErrors(['customer_id' => 'Nota besar wajib memilih pelanggan.'])->withInput();
        }

        try {
            $order = $database->transaction(function () use ($data, $request): Order {
                $items = collect($data['items']);
                $products = Product::whereIn('id', $items->pluck('product_id'))->get()->keyBy('id');
                $subtotal = '0.00';
                $lines = [];

                foreach ($items as $item) {
                    $product = $products->get($item['product_id']);
                    $unitPrice = $data['document_type'] === 'hotel' ? $product->hotel_price : $product->store_price;
                    $lineTotal = number_format((float) $unitPrice * (float) $item['quantity'], 2, '.', '');
                    $subtotal = number_format((float) $subtotal + (float) $lineTotal, 2, '.', '');
                    $lines[] = compact('product', 'item', 'unitPrice', 'lineTotal');
                }

                $discountType = $data['discount_type'] ?? null;
                $discountValue = (string) ($data['discount_value'] ?? '0');
                $discountAmount = $discountType === 'percentage'
                    ? number_format((float) $subtotal * (float) $discountValue / 100, 2, '.', '')
                    : number_format((float) $discountValue, 2, '.', '');

                if ((float) $discountAmount > (float) $subtotal) {
                    throw new \InvalidArgumentException('Diskon tidak boleh melebihi subtotal.');
                }

                $taxableTotal = number_format((float) $subtotal - (float) $discountAmount, 2, '.', '');
                $taxBase = number_format((float) $taxableTotal / 1.11, 2, '.', '');
                $taxAmount = number_format((float) $taxableTotal - (float) $taxBase, 2, '.', '');

                $order = Order::create([
                    'customer_id' => $data['customer_id'] ?? null,
                    'created_by' => $request->user()->id,
                    'document_type' => $data['document_type'],
                    'document_number' => (string) $this->nextDocumentNumber($data['document_type']),
                    'status' => 'draft',
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'total' => $taxableTotal,
                ]);

                foreach ($lines as $line) {
                    $balance = StockBalance::query()->lockForUpdate()->firstOrCreate(['product_id' => $line['product']->id]);
                    $available = (float) $balance->physical_quantity - (float) $balance->reserved_quantity;
                    $quantity = (string) $line['item']['quantity'];

                    if ($available < (float) $quantity) {
                        throw new \InvalidArgumentException("Stok {$line['product']->name} tidak mencukupi.");
                    }

                    $order->items()->create([
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->name,
                        'quantity' => $line['item']['quantity'],
                        'unit' => $line['product']->unit,
                        'unit_price' => $line['unitPrice'],
                        'master_unit_price' => $line['unitPrice'],
                        'price_difference' => '0.00',
                        'line_total' => $line['lineTotal'],
                    ]);
                    $balance->update(['reserved_quantity' => number_format((float) $balance->reserved_quantity + (float) $quantity, 3, '.', '')]);
                    StockReservation::create([
                        'product_id' => $line['product']->id,
                        'order_id' => $order->id,
                        'quantity' => $quantity,
                        'status' => 'reserved',
                    ]);
                }

                return $order;
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['order' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Order gagal disimpan. Periksa data lalu coba lagi.'])->withInput();
        }

        return redirect()->route('orders.index')->with('success', "Order {$order->document_number} berhasil dibuat.");
    }
}
