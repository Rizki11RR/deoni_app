<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $title }} | {{ config('app.name') }}</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body><main class="page page-center"><div class="container container-tight py-4"><div class="empty"><div class="empty-header">{{ $code }}</div><p class="empty-title">{{ $title }}</p><p class="empty-subtitle text-secondary">{{ $message }}</p><div class="empty-action"><a class="btn btn-primary" href="{{ auth()->check() ? route('dashboard') : route('login') }}">Kembali</a></div></div></div></main></body></html>
