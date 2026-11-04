@if (session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <div>{{ session('success') }}</div>
        <button class="btn-close" data-bs-dismiss="alert" type="button" aria-label="Tutup"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
        <div><strong>Periksa input:</strong><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        <button class="btn-close" data-bs-dismiss="alert" type="button" aria-label="Tutup"></button>
    </div>
@endif
