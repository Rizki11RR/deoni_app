<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Log audit</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
<main class="page-wrapper"><div class="container-xl py-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="mb-0">Log audit</h1><a class="btn btn-secondary" href="{{ route('dashboard') }}">Dashboard</a></div>
    <x-flash />
    <div class="card"><div class="table-responsive"><table class="table table-vcenter card-table"><thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Request</th><th>Status</th><th>Data</th></tr></thead><tbody>
    @forelse($logs as $log)<tr><td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td><td>{{ $log->user?->name ?? 'Sistem/guest' }}</td><td>{{ $log->action }}</td><td>{{ $log->method }} {{ $log->path }}</td><td>{{ $log->status_code }}</td><td><details><summary>Lihat</summary><pre class="small mb-0">{{ json_encode($log->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details></td></tr>@empty<tr><td colspan="6" class="text-center text-secondary py-4">Belum ada aktivitas tercatat.</td></tr>@endforelse
    </tbody></table></div><div class="card-footer">{{ $logs->links() }}</div></div>
</div></main>
</body></html>
