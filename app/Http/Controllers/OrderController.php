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
            'products' => Product::with(['uom', 'packagings.uom'])->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function approve(Request $request, Order $order, DatabaseManager $database): RedirectResponse
    {
        try {
            $database->transaction(function () use ($request, $order): void {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

                if ($lockedOrder->document_type !== 'store') {
                    throw new \InvalidArgumentException('Hanya bon yang dapat disetujui.');
                }

                if ($lockedOrder->status !== 'draft') {
                    throw new \InvalidArgumentException('Hanya bon berstatus pending yang dapat disetujui.');
                }

                $lockedOrder->update(['status' => 'approved', 'approved_by' => $request->user()->id, 'approved_at' => now()]);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['order' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Bon gagal disetujui.'])->withInput();
        }

        return back()->with('success', "Bon {$order->document_number} berhasil disetujui.");
    }

    public function print(Order $order): View
    {
        return view('orders.print', ['order' => $order->load(['customer', 'creator', 'items', 'payments'])]);
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
            'items.*.uom_id' => ['nullable', 'exists:uoms,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'discount_type' => ['nullable', 'in:amount,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        if ($data['document_type'] !== 'pos' && empty($data['customer_id'])) {
            return back()->withErrors(['customer_id' => 'Nota besar wajib memilih pelanggan.'])->withInput();
        }

        try {
            $order = $database->transaction(function () use ($data, $request): Order {
                $items = collect($data['items']);
                $products = Product::with(['uom.category', 'salesUom.category', 'packagings.uom.category'])->where('is_active', true)->whereIn('id', $items->pluck('product_id'))->get()->keyBy('id');
                $subtotal = '0.00';
                $lines = [];

                foreach ($items as $item) {
                    $product = $products->get($item['product_id']);

                    if ($product === null) {
                        throw new \InvalidArgumentException('Produk tidak aktif atau tidak ditemukan.');
                    }

                    $uom = ! empty($item['uom_id']) ? $product->packagings->firstWhere('uom_id', $item['uom_id']) : null;
                    $selectedUom = $uom?->uom ?? ($product->salesUom ?: $product->uom);
                    if ($selectedUom !== null && (! $selectedUom->is_active || ! $selectedUom->category?->is_active || ($product->uom !== null && $selectedUom->uom_category_id !== $product->uom->uom_category_id))) {
                        throw new \InvalidArgumentException("Satuan produk {$product->name} tidak valid.");
                    }
                    if ($item['uom_id'] ?? null) {
                        if ($uom === null || ! $uom->is_active) {
                            throw new \InvalidArgumentException("Kemasan produk {$product->name} tidak aktif.");
                        }
                        $factor = (float) $uom->quantity;
                        $unitPrice = $data['document_type'] === 'hotel' ? $uom->hotel_price : $uom->store_price;
                        if ($unitPrice === null) {
                            throw new \InvalidArgumentException("Harga kemasan produk {$product->name} belum diatur.");
                        }
                    } else {
                        $factor = $selectedUom === null
                            ? 1
                            : ($selectedUom->conversion_type === 'divide' ? 1 / (float) $selectedUom->conversion_factor : (float) $selectedUom->conversion_factor);
                        $unitPrice = $data['document_type'] === 'hotel' ? $product->hotel_price : $product->store_price;
                    }
                    $baseQuantity = (float) $item['quantity'] * $factor;
                    $lineTotal = number_format((float) $unitPrice * (float) $item['quantity'], 2, '.', '');
                    $subtotal = number_format((float) $subtotal + (float) $lineTotal, 2, '.', '');
                    $lines[] = ['product' => $product, 'item' => $item, 'uom' => $selectedUom, 'factor' => $factor, 'baseQuantity' => $baseQuantity, 'unitPrice' => $unitPrice, 'lineTotal' => $lineTotal];
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
                    'due_date' => $data['due_date'] ?? null,
                    'document_type' => $data['document_type'],
                    'document_number' => (string) $this->nextDocumentNumber($data['document_type']),
                    'status' => 'draft',
                    'preparation_status' => 'pending',
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'total' => $taxableTotal,
                ]);

                foreach ($lines as $line) {
                    $balance = StockBalance::query()->lockForUpdate()->firstOrCreate(['product_id' => $line['product']->id]);
                    $available = (float) $balance->physical_quantity - (float) $balance->reserved_quantity;
                    $quantity = number_format((float) $line['baseQuantity'], 6, '.', '');

                    if ($available < (float) $quantity) {
                        throw new \InvalidArgumentException("Stok {$line['product']->name} tidak mencukupi.");
                    }

                    $order->items()->create([
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->name,
                        'quantity' => $line['item']['quantity'],
                        'base_quantity' => $line['baseQuantity'],
                        'uom_id' => $line['uom']?->id,
                        'unit' => $line['product']->unit,
                        'uom_symbol' => $line['uom']?->symbol,
                        'uom_factor' => $line['factor'],
                        'unit_price' => $line['unitPrice'],
                        'master_unit_price' => $line['unitPrice'],
                        'price_difference' => '0.00',
                        'line_total' => $line['lineTotal'],
                    ]);
                    $balance->update(['reserved_quantity' => number_format((float) $balance->reserved_quantity + (float) $quantity, 3, '.', '')]);
                    StockReservation::create(['product_id' => $line['product']->id, 'order_id' => $order->id, 'quantity' => $quantity, 'status' => 'reserved']);
                }

                return $order;
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['order' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Order gagal disimpan. Periksa data lalu coba lagi.'])->withInput();
        }

        return redirect()->route('orders.index')->with('success', "Order {$order->document_number} berhasil dibuat.");
    }
}
