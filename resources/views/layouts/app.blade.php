<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif{{ $business['name'] }}</title>
    <meta name="description" content="Sewa mobil di Makassar - lepas kunci atau dengan sopir. {{ $business['hours'] }}.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 font-sans text-slate-800 antialiased">
<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-extrabold text-brand-700">
            <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-600 text-white">P</span>
            <span>Padaidi <span class="font-medium text-slate-500">Rental Mobil</span></span>
        </a>
        <button type="button" class="rounded-lg p-2 text-slate-600 md:hidden" data-toggle="#nav-mobile" aria-label="Buka menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
            <a href="{{ route('cars.index') }}" class="hover:text-brand-600">Armada</a>
            <a href="{{ route('terms') }}" class="hover:text-brand-600">Syarat Sewa</a>
            <a href="{{ route('contact') }}" class="hover:text-brand-600">Kontak</a>
            <a href="{{ route('bookings.lookup') }}" class="btn btn-outline">Cek Pesanan</a>

            @auth
                @if (auth()->user()->isMember())
                    @php
                        $userTier = auth()->user()->tier();
                    @endphp
                    <div class="flex items-center gap-3 pl-2 border-l border-slate-200">
                        <a href="{{ route('member.dashboard') }}" class="flex items-center gap-2 hover:text-brand-700">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-100 text-brand-800 text-xs font-bold">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                            <div class="text-left text-xs leading-tight">
                                <span class="font-bold text-slate-800 block">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-amber-700 font-semibold">{{ $userTier?->name ?? 'Member' }}</span>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('member.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-slate-400 hover:text-rose-600" title="Keluar">
                                Keluar
                            </button>
                        </form>
                    </div>
                @elseif (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary text-xs">Panel Admin &rarr;</a>
                @endif
            @else
                <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                    <a href="{{ route('member.login') }}" class="hover:text-brand-700 text-xs font-semibold">Masuk</a>
                    <a href="{{ route('member.register') }}" class="btn btn-primary text-xs py-1.5 px-3">Daftar Member</a>
                </div>
            @endauth
        </nav>
    </div>
    <nav id="nav-mobile" class="hidden border-t border-slate-200 px-4 py-2 text-sm font-medium md:hidden">
        <a href="{{ route('cars.index') }}" class="block py-2">Armada</a>
        <a href="{{ route('terms') }}" class="block py-2">Syarat Sewa</a>
        <a href="{{ route('contact') }}" class="block py-2">Kontak</a>
        <a href="{{ route('bookings.lookup') }}" class="block py-2">Cek Pesanan</a>

        <div class="border-t border-slate-200 pt-2 mt-2">
            @auth
                @if (auth()->user()->isMember())
                    <div class="py-2">
                        <span class="text-xs text-slate-500 block">Masuk sebagai:</span>
                        <span class="font-bold text-slate-800 text-sm">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-brand-700 font-semibold">({{ auth()->user()->tier()?->name ?? 'Member' }})</span>
                    </div>
                    <a href="{{ route('member.dashboard') }}" class="block py-1.5 text-brand-700 font-bold">Akun Saya</a>
                    <a href="{{ route('member.bookings') }}" class="block py-1.5">Riwayat Pesanan</a>
                    <a href="{{ route('member.profile') }}" class="block py-1.5">Ubah Profil</a>
                    <a href="{{ route('member.documents') }}" class="block py-1.5">Dokumen e-KYC</a>
                    <form method="POST" action="{{ route('member.logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-outline w-full text-xs text-rose-600 border-rose-300 hover:bg-rose-50">Keluar</button>
                    </form>
                @elseif (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary w-full text-center text-xs">Panel Admin</a>
                @endif
            @else
                <div class="grid grid-cols-2 gap-2 py-2">
                    <a href="{{ route('member.login') }}" class="btn btn-outline text-center text-xs">Masuk</a>
                    <a href="{{ route('member.register') }}" class="btn btn-primary text-center text-xs">Daftar Member</a>
                </div>
            @endauth
        </div>
    </nav>
</header>

<main class="flex-1">
    @hasSection('hero')
        @yield('hero')
    @endif
    <div class="mx-auto max-w-6xl px-4 py-6">
        @include('partials.flash')
        @yield('content')
    </div>
</main>

<footer class="mt-10 bg-brand-900 text-slate-300">
    <div class="mx-auto grid max-w-6xl gap-6 px-4 py-8 text-sm md:grid-cols-3">
        <div>
            <p class="text-base font-bold text-white">{{ $business['name'] }}</p>
            <p class="mt-2">{{ $business['address'] }}</p>
            <p class="mt-1">{{ $business['hours'] }}</p>
        </div>
        <div>
            <p class="font-semibold text-white">Hubungi kami</p>
            @foreach ($business['admin_whatsapp'] as $wa)
                <a class="mt-1 block hover:text-white" href="{{ \App\Support\WhatsApp::link($wa, 'Halo Padaidi, saya ingin bertanya tentang sewa mobil.') }}" target="_blank" rel="noopener">WhatsApp {{ \App\Support\WhatsApp::display($wa) }}</a>
            @endforeach
            <a class="mt-1 block hover:text-white" href="mailto:{{ $business['email'] }}">{{ $business['email'] }}</a>
        </div>
        <div>
            <p class="font-semibold text-white">Tautan</p>
            <a class="mt-1 block hover:text-white" href="{{ route('cars.index') }}">Daftar armada</a>
            <a class="mt-1 block hover:text-white" href="{{ route('terms') }}">Syarat sewa</a>
            <a class="mt-1 block hover:text-white" href="{{ route('bookings.lookup') }}">Cek status pesanan</a>
        </div>
    </div>
    <p class="border-t border-white/10 py-4 text-center text-xs">&copy; {{ date('Y') }} {{ $business['name'] }}</p>
</footer>
@stack('scripts')
</body>
</html>
