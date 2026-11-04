<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Catat stok</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
<script>document.addEventListener('DOMContentLoaded', () => { document.querySelectorAll('[data-confirm]').forEach((element) => element.addEventListener('click', (event) => { if (!window.confirm(element.dataset.confirm)) event.preventDefault(); })); });</script>
<main class="page page-center"><div class="container container-tight py-4"><form class="card" method="POST" action="{{ route('stock.store') }}">@csrf
    <div class="card-header"><h1 class="card-title">Catat perubahan stok</h1></div><div class="card-body"><x-flash />
    <label class="form-label">Produk<select class="form-select" name="product_id" required><option value="">Pilih produk</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }} ({{ $product->unit }})</option>@endforeach</select></label>
    <label class="form-label mt-3">Jenis perubahan<select class="form-select" name="type" required><option value="in" @selected(old('type') === 'in')>Stok masuk</option><option value="out" @selected(old('type') === 'out')>Stok keluar</option><option value="adjustment" @selected(old('type') === 'adjustment')>Penyesuaian keluar</option></select></label>
    <label class="form-label mt-3">Jumlah<input class="form-control" name="quantity" type="number" min="0.001" step="0.001" value="{{ old('quantity') }}" required></label>
    <label class="form-label mt-3">Alasan<textarea class="form-control" name="reason" minlength="3" maxlength="1000" required>{{ old('reason') }}</textarea></label>
    </div><div class="card-footer d-flex gap-2"><a class="btn btn-secondary" href="{{ route('stock.index') }}">Batal</a><button class="btn btn-primary" data-confirm="Simpan perubahan stok ini?" type="submit">Simpan stok</button></div>
</form></div></main>
</body></html>
