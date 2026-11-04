<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Order</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<main class="page page-center">
    <div class="container container-tight py-4">
        <form class="card" method="POST" action="{{ route('orders.store') }}" data-order-form>
            @csrf
            <div class="card-header"><h2 class="card-title">Buat order</h2></div>
            <div class="card-body">
                <x-flash />
                <label class="form-label">Jenis nota
                    <select class="form-select" name="document_type" required>
                        <option value="pos">Nota Kecil/POS</option>
                        <option value="store">Nota Toko</option>
                        <option value="hotel">Nota Hotel</option>
                        <option value="sales">Nota Sales</option>
                    </select>
                </label>
                <label class="form-label mt-3">Pelanggan
                    <select class="form-select" name="customer_id">
                        <option value="">POS anonim</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="row mt-3">
                    <div class="col-md-6"><label class="form-label">Jenis diskon<select class="form-select" name="discount_type"><option value="">Tidak ada</option><option value="amount">Nominal</option><option value="percentage">Persentase</option></select></label></div>
                    <div class="col-md-6"><label class="form-label">Nilai diskon<input class="form-control" name="discount_value" type="number" min="0" step="0.01" value="0"></label></div>
                </div>
                <label class="form-label mt-3">Jatuh tempo <span class="text-secondary">(opsional)</span><input class="form-control" name="due_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('due_date') }}"></label>
                <div class="mt-3"><label class="form-label">Produk</label>
                    @foreach($products as $product)
                        <div class="border rounded p-2 mb-2 order-product-row">
                            <div class="form-check"><input class="form-check-input product-toggle" name="items[{{ $loop->index }}][product_id]" type="checkbox" value="{{ $product->id }}"><label class="form-check-label">{{ $product->name }} (stok {{ $product->uom?->symbol ?? $product->unit }})</label></div>
                            <div class="row g-2 mt-1">
                                <div class="col-md-5"><select class="form-select" name="items[{{ $loop->index }}][uom_id]" aria-label="Satuan {{ $product->name }}">
                                    <option value="">{{ $product->salesUom?->name ?? $product->uom?->name ?? $product->unit }}</option>
                                    @foreach($product->packagings as $packaging)
                                        @if($packaging->is_active && $packaging->uom?->is_active)<option value="{{ $packaging->uom_id }}">{{ $packaging->name }} ({{ $packaging->uom?->symbol }})</option>@endif
                                    @endforeach
                                </select></div>
                                <div class="col-md-3"><input class="form-control" name="items[{{ $loop->index }}][quantity]" type="number" min="0" step="0.001" value="0" aria-label="Jumlah {{ $product->name }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer"><button class="btn btn-primary" data-confirm="Simpan order ini?" type="submit">Simpan order</button></div>
        </form>
    </div>
</main>
</body>
</html>
