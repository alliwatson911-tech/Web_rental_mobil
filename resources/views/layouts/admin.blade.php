<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'Dasbor') · Admin Padaidi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
@php
    $menu = [
        ['admin.dashboard', 'Dasbor', 'admin.dashboard'],
        ['admin.bookings.index', 'Pesanan', 'admin.bookings.*'],
        ['admin.payments.index', 'Pembayaran', 'admin.payments.*'],
        ['admin.cars.index', 'Armada', 'admin.cars.*'],
        ['admin.blocks.index', 'Jadwal Servis', 'admin.blocks.*'],
        ['admin.addons.index', 'Tambahan', 'admin.addons.*'],
        ['admin.locations.index', 'Lokasi', 'admin.locations.*'],
    ];
@endphp
<div class="md:flex">
    <aside class="bg-brand-900 text-slate-200 md:min-h-screen md:w-60 md:shrink-0">
        <div class="flex items-center justify-between px-4 py-4">
            <a href="{{ route('admin.dashboard') }}" class="font-extrabold text-white">Padaidi · Admin</a>
            <button type="button" class="md:hidden" data-toggle="#admin-nav" aria-label="Menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <nav id="admin-nav" class="hidden space-y-1 px-2 pb-4 text-sm md:block">
            @foreach ($menu as [$route, $label, $pattern])
                <a href="{{ route($route) }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs($pattern) ? 'bg-white/15 font-semibold text-white' : 'hover:bg-white/10' }}">{{ $label }}</a>
            @endforeach
            <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 hover:bg-white/10" target="_blank">Lihat situs ↗</a>
            <form method="POST" action="{{ route('admin.logout') }}" class="px-3 pt-3">
                @csrf
                <p class="mb-2 text-xs text-slate-400">Masuk sebagai {{ auth()->user()->name }}</p>
                <button class="btn btn-outline w-full">Keluar</button>
            </form>
        </nav>
    </aside>
    <main class="flex-1 p-4 md:p-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-slate-900">@yield('title', 'Dasbor')</h1>
            @yield('actions')
        </div>
        @include('partials.flash')
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
