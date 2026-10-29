@extends('layouts.app')
@section('title', 'Masuk Member - Padaidi Rent Car')

@section('content')
<div class="mx-auto max-w-md py-10 px-4">
    <div class="card card-accent-top p-8 shadow-sm border border-slate-200 bg-white relative">
        <div class="text-center">
            <a href="{{ route('home') }}" class="inline-block mb-3" title="Padaidi Rent Car">
                <img src="{{ asset('images/brand/padaidi-logo.png') }}" alt="Padaidi Rent Car" class="h-14 w-auto mx-auto object-contain">
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-wide uppercase">Masuk Member</h1>
            <p class="mt-1 text-sm text-slate-500">Akses riwayat pesanan, tier diskon, dan promo sewa mobil.</p>
        </div>

        <form method="POST" action="{{ route('login.submit') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="label text-slate-700 font-bold" for="email">Email atau Nomor WhatsApp</label>
                <input class="input" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@domain.com atau 08xxxxxxxxxx">
                @error('email')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label class="label text-slate-700 font-bold" for="password">Kata Sandi</label>
                    <a href="{{ route('password.request') }}" class="text-xs text-amber-600 hover:text-amber-800 font-semibold hover:underline">Lupa sandi?</a>
                </div>
                <input class="input" type="password" id="password" name="password" required placeholder="••••••••">
                @error('password')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-[#F5A623] focus:ring-[#F5A623]">
                    <span class="text-xs font-medium">Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-full py-3 font-extrabold uppercase tracking-wider text-xs shadow-md">Masuk ke Akun</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            Belum punya akun member? <a href="{{ route('register') }}" class="font-bold text-amber-600 hover:text-amber-800 hover:underline">Daftar Member Baru</a>
        </p>

        <div class="mt-6 border-t border-slate-200 pt-4 text-center">
            <a href="{{ route('admin.login') }}" class="text-xs text-slate-400 hover:text-amber-600 transition-colors">Masuk sebagai Pengelola / Admin ↗</a>
        </div>
    </div>
</div>
@endsection
