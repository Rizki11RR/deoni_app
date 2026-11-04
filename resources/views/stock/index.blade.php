<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Stok</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
<script>document.addEventListener('DOMContentLoaded', () => { document.querySelectorAll('[data-confirm]').forEach((element) => element.addEventListener('click', (event) => { if (!window.confirm(element.dataset.confirm)) event.preventDefault(); })); });</script>
<main class="page-wrapper"><div class="container-xl py-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="mb-0">Stok</h1><a class="btn btn-primary" href="{{ route('stock.create') }}">Catat perubahan stok</a></div>
    <x-flash />
    <div class="card"><div class="table-responsive"><table class="table table-vcenter card-table"><thead><tr><th>Produk</th><th>Fisik</th><th>Reservasi</th><th>Tersedia</th><th>Verifikasi</th></tr></thead><tbody>
    @forelse($balances as $balance)<tr><td>{{ $balance->product->name }}</td><td>{{ number_format((float) $balance->physical_quantity, 3, ',', '.') }} {{ $balance->product->unit }}</td><td>{{ number_format((float) $balance->reserved_quantity, 3, ',', '.') }}</td><td>{{ number_format((float) $balance->available_quantity, 3, ',', '.') }}</td><td>{{ $balance->is_verified ? 'Terverifikasi' : 'Belum diverifikasi' }}</td></tr>@empty<tr><td colspan="5" class="text-center text-secondary py-4">Belum ada saldo stok. Catat stok masuk untuk memulai.</td></tr>@endforelse
    </tbody></table></div></div>
</div></main>
</body></html>
