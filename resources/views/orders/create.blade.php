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
    <div class="container-xl py-4 py-md-5">
        <form method="POST" action="{{ route('orders.store') }}" data-order-form>
            @csrf

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <div class="text-secondary text-uppercase small fw-bold">Penjualan</div>
                    <h1 class="mb-1">Buat order</h1>
                    <div class="text-secondary">Isi informasi order, lalu pilih produk yang dijual.</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Batal</a>
                    <button class="btn btn-primary" data-confirm="Simpan order ini?" type="submit">Simpan order</button>
                </div>
            </div>

            <x-flash />

            <section class="card mb-4" aria-labelledby="order-details-heading">
                <div class="card-header">
                    <h2 class="card-title" id="order-details-heading">Informasi order</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-lg-4">
                            <label class="form-label" for="document-type">Jenis nota <span class="text-danger">*</span></label>
                            <select class="form-select" id="document-type" name="document_type" required>
                                <option value="pos" @selected(old('document_type', 'pos') === 'pos')>Nota Kecil/POS</option>
                                <option value="store" @selected(old('document_type') === 'store')>Nota Toko</option>
                                <option value="hotel" @selected(old('document_type') === 'hotel')>Nota Hotel</option>
                                <option value="sales" @selected(old('document_type') === 'sales')>Nota Sales</option>
                            </select>
                        </div>
                        <div class="col-12 col-lg-5">
                            <label class="form-label" for="customer">Pelanggan</label>
                            <select class="form-select" id="customer" name="customer_id">
                                <option value="">POS anonim</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-lg-3">
                            <label class="form-label" for="due-date">Jatuh tempo</label>
                            <input class="form-control" id="due-date" name="due_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('due_date') }}">
                        </div>
                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="discount-type">Diskon</label>
                            <select class="form-select" id="discount-type" name="discount_type">
                                <option value="">Tidak ada</option>
                                <option value="amount" @selected(old('discount_type') === 'amount')>Nominal</option>
                                <option value="percentage" @selected(old('discount_type') === 'percentage')>Persentase</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="discount-value">Nilai diskon</label>
                            <input class="form-control" id="discount-value" name="discount_value" type="number" min="0" step="0.01" value="{{ old('discount_value', 0) }}">
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="text-secondary small pb-2">Harga dan total dihitung saat order diproses.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card" aria-labelledby="products-heading">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h2 class="card-title mb-1" id="products-heading">Produk order</h2>
                        <div class="text-secondary small">Centang produk yang masuk order. Produk tanpa centang tidak dikirim.</div>
                    </div>
                    <span class="badge bg-blue-lt">{{ $products->count() }} produk tersedia</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <caption class="visually-hidden">Daftar produk untuk order</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="w-50">Produk</th>
                                <th scope="col">UoM / packaging</th>
                                <th scope="col" class="text-end">Jumlah</th>
                                <th scope="col" class="text-end">Harga satuan</th>
                                <th scope="col">Info baris</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($products as $product)
                            @php($itemIndex = $loop->index)
                            <tr class="order-product-row">
                                <td>
                                    <div class="d-flex align-items-start gap-3">
                                        <input class="form-check-input product-toggle mt-1" id="product-{{ $product->id }}" name="items[{{ $itemIndex }}][product_id]" type="checkbox" value="{{ $product->id }}">
                                        <div>
                                            <label class="form-label mb-1 fw-semibold" for="product-{{ $product->id }}">{{ $product->name }}</label>
                                            @if($product->code)<div class="text-secondary small">Kode: {{ $product->code }}</div>@endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <label class="visually-hidden" for="uom-{{ $product->id }}">Satuan {{ $product->name }}</label>
                                    <select class="form-select" id="uom-{{ $product->id }}" name="items[{{ $itemIndex }}][uom_id]">
                                        <option value="">{{ $product->salesUom?->name ?? $product->uom?->name ?? $product->unit }}</option>
                                        @foreach($product->packagings as $packaging)
                                            @if($packaging->is_active && $packaging->uom?->is_active)
                                                <option value="{{ $packaging->uom_id }}">{{ $packaging->name }} ({{ $packaging->uom?->symbol }})</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <label class="visually-hidden" for="quantity-{{ $product->id }}">Jumlah {{ $product->name }}</label>
                                    <input class="form-control text-end" id="quantity-{{ $product->id }}" name="items[{{ $itemIndex }}][quantity]" type="number" min="0" step="0.001" value="{{ old("items.{$itemIndex}.quantity", 0) }}">
                                </td>
                                <td class="text-end text-secondary">{{ $product->store_price !== null ? number_format($product->store_price, 2, ',', '.') : 'Menunggu harga' }}</td>
                                <td class="text-secondary small">Stok {{ $product->uom?->symbol ?? $product->unit }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-5">Belum ada produk aktif.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button class="btn btn-primary" data-confirm="Simpan order ini?" type="submit">Simpan order</button>
                </div>
            </section>
        </form>
    </div>
</main>
</body>
</html>
