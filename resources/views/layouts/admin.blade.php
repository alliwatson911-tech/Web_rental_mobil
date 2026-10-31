<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'Dasbor') · Admin Padaidi Rent Car</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    {{-- Google Font: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
</head>
<body class="theme-admin min-h-screen bg-[#0b1220] font-sans text-slate-100 antialiased" style="background-color: #0b1220; color: #f8fafc;">
@php
    $menu = [
        ['admin.dashboard', 'Dasbor', 'admin.dashboard'],
        ['admin.bookings.index', 'Pesanan', 'admin.bookings.*'],
        ['admin.payments.index', 'Pembayaran', 'admin.payments.*'],
        ['admin.cars.index', 'Armada', 'admin.cars.*'],
        ['admin.blocks.index', 'Jadwal Servis', 'admin.blocks.*'],
        ['admin.addons.index', 'Tambahan', 'admin.addons.*'],
        ['admin.locations.index', 'Lokasi', 'admin.locations.*'],
        ['admin.members.index', 'Member Pelanggan', ['admin.members.index', 'admin.members.show']],
        ['admin.promotions.index', 'Diskon & Promo', ['admin.promotions.index', 'admin.promotions.create', 'admin.promotions.edit']],
        ['admin.promotions.report', 'Laporan Promo', 'admin.promotions.report'],
        ['admin.members.logs', 'Audit Akses Dokumen', 'admin.members.logs'],
    ];
@endphp
<div class="min-h-screen md:flex">
    <aside class="admin-sidebar sticky top-0 z-50 bg-[#0b1220] border-b border-[#1e293b] text-slate-200 md:border-b-0 md:border-r md:min-h-screen md:w-64 md:shrink-0 md:h-screen md:overflow-y-auto" style="position: -webkit-sticky; position: sticky; top: 0; z-index: 1000; background-color: #0b1220; border-color: #1e293b;">
        <div class="flex items-center justify-between px-4 py-3.5 border-b border-[#1e293b] bg-[#0b1220]">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 text-xs font-black tracking-wider text-white uppercase whitespace-nowrap shrink-0">
                <img src="{{ asset('images/brand/padaidi-logo.png') }}" alt="Padaidi Emblem" class="h-8 w-auto object-contain shrink-0" width="32" height="32" />
                <span><span class="text-[#F5A623]">PADAIDI</span> ADMIN</span>
            </a>
            <button type="button" class="md:hidden text-slate-400 hover:text-white p-2 min-h-[44px] min-w-[44px] flex items-center justify-center cursor-pointer rounded border border-[#1e293b] bg-[#111827]" data-toggle="#admin-nav" aria-label="Menu Navigasi Admin" aria-controls="admin-nav" aria-expanded="false">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <nav id="admin-nav" class="hidden space-y-1 px-3 py-4 text-xs font-bold uppercase tracking-wider md:block max-h-[calc(100dvh-65px)] overflow-y-auto">
            @foreach ($menu as [$route, $label, $patterns])
                @php
                    $isActive = is_array($patterns) ? request()->routeIs(...$patterns) : request()->routeIs($patterns);
                @endphp
                <a href="{{ route($route) }}" class="block rounded px-3 py-2.5 transition {{ $isActive ? 'bg-[#F5A623] font-black text-[#0b1220] shadow-sm' : 'text-slate-300 hover:bg-[#1e293b] hover:text-[#F5A623]' }}">{{ $label }}</a>
            @endforeach
            <div class="pt-4 border-t border-[#1e293b] mt-4">
                <a href="{{ route('home') }}" class="block rounded px-3 py-2 text-slate-300 hover:bg-[#1e293b] hover:text-[#F5A623] transition" target="_blank">Lihat situs ↗</a>
                <form method="POST" action="{{ route('admin.logout') }}" class="pt-3">
                    @csrf
                    <p class="mb-2 text-[11px] text-slate-400">Masuk sebagai <strong class="text-white">{{ auth()->user()->name }}</strong></p>
                    <button class="btn btn-outline w-full text-xs py-1.5 border-[#334155] text-slate-300 bg-[#1e293b] hover:bg-[#334155] hover:text-white">Keluar</button>
                </form>
            </div>
        </nav>
    </aside>
    <main class="flex-1 p-4 md:p-8 bg-[#0b1220]" style="background-color: #0b1220;">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-black uppercase tracking-wide text-white">@yield('title', 'Dasbor')</h1>
            @yield('actions')
        </div>
        @include('partials.flash')
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
