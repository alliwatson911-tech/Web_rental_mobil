<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Masuk Admin · Padaidi Rent Car</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
</head>
<body class="theme-padaidi grid min-h-screen place-items-center bg-[#0b1220] p-4 font-sans text-[#f8fafc] relative overflow-hidden" style="position: relative; overflow: hidden; isolation: isolate;">
    {{-- Ambient Decorative Background Circle --}}
    <div class="ambient-circle-bg" style="position: absolute; top: -150px; right: -150px; z-index: -1; pointer-events: none;"></div>

    <div class="card w-full max-w-md p-8 shadow-2xl border border-[#1e293b] bg-[#111827] card-accent-top relative z-10">
        {{-- Brand Logo & Heading --}}
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-block group mb-3">
                <img src="{{ asset('images/brand/padaidi-logo.png') }}" 
                     alt="Padaidi Rent Car Logo" 
                     class="h-16 w-auto mx-auto object-contain transition-transform group-hover:scale-105" />
            </a>
            <span class="section-kicker block">PANEL KHUSUS PENGELOLA</span>
            <h1 class="text-2xl font-black uppercase tracking-wider text-[#f8fafc] m-0">Masuk Admin</h1>
        </div>

        <div class="mb-4">@include('partials.flash')</div>

        <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label text-xs uppercase font-bold tracking-wider text-[#94a3b8]" for="email">Alamat Email</label>
                <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@padaidi.test">
            </div>
            <div>
                <label class="label text-xs uppercase font-bold tracking-wider text-[#94a3b8]" for="password">Kata Sandi</label>
                <div class="relative">
                    <input class="input pr-10" type="password" id="password" name="password" required placeholder="••••••••">
                    <button type="button" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';" class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#64748b] hover:text-[#f8fafc]" title="Tampilkan/sembunyikan kata sandi">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>
            <label class="flex items-center gap-2 text-xs font-semibold text-[#94a3b8]">
                <input type="checkbox" name="remember" value="1" class="rounded border-[#1e293b] bg-[#0b1220] text-[#F5A623] focus:ring-[#F5A623]">
                Ingat saya di perangkat ini
            </label>
            <button class="btn-padaidi btn-padaidi-primary w-full py-3 text-sm font-extrabold uppercase tracking-wider">
                Masuk ke Dasbor Admin
            </button>
        </form>

        <div class="mt-6 rounded border border-[#1e293b] bg-[#0b1220] p-3 text-xs text-[#94a3b8]">
            <p class="font-bold text-[#F5A623] uppercase tracking-wider text-[11px]">Akun Pengujian:</p>
            <ul class="mt-1 list-disc pl-4 space-y-0.5 text-[#94a3b8]">
                <li><code>admin@padaidi.test</code> (Admin 1)</li>
                <li><code>admin2@padaidi.test</code> (Admin 2)</li>
            </ul>
            <p class="mt-1.5 text-[11px] text-[#64748b]">Sandi: <code>admin123</code> / <code>padaidi-admin-123</code></p>
        </div>

        <a href="{{ route('home') }}" class="mt-6 block text-center text-xs font-bold uppercase tracking-wider text-[#94a3b8] hover:text-[#F5A623] transition">
            ← Kembali ke Beranda Situs
        </a>
    </div>
</body>
</html>
