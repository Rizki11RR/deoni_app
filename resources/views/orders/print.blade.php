<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Nota {{ $order->document_number }}</title>@vite(['resources/css/app.css','resources/js/app.js'])<style>
        .receipt-copies { display: grid; gap: 1rem; }
        .receipt { border: 1px solid #d9dee5; padding: 1rem; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; font-family: Arial, sans-serif; }
            .receipt-copies { display: block; }
            .receipt { border: 0; break-after: page; page-break-after: always; }
            .receipt:last-child { break-after: auto; page-break-after: auto; }
        }
    </style></head><body><main class="container py-4"><div class="d-flex justify-content-between no-print mb-3"><a class="btn btn-secondary" href="{{ route('orders.index') }}">Kembali</a><button class="btn btn-primary" type="button" onclick="window.print()">Cetak nota</button></div><div class="receipt-copies">@foreach(['SALINAN TOKO', 'SALINAN PELANGGAN'] as $copyLabel)<section class="receipt"><h1>Cake Store Management</h1><p><strong>{{ $copyLabel }}</strong></p><p class="mb-1">Nota: <strong>{{ $order->document_number }}</strong></p><p class="mb-3">Pelanggan: {{ $order->customer?->name ?: 'POS anonim' }}</p><table class="table"><thead><tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Total</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }} {{ $item->unit }}</td><td>Rp {{ number_format($item->unit_price, 2, ',', '.') }}</td><td>Rp {{ number_format($item->line_total, 2, ',', '.') }}</td></tr>@endforeach</tbody></table><div class="text-end"><p>Subtotal: Rp {{ number_format($order->subtotal, 2, ',', '.') }}</p><p>Diskon: Rp {{ number_format($order->discount_amount, 2, ',', '.') }}</p><p>Pajak: Rp {{ number_format($order->tax_amount, 2, ',', '.') }}</p><h2>Total: Rp {{ number_format($order->total, 2, ',', '.') }}</h2></div></section>@endforeach</div></main></body></html>
