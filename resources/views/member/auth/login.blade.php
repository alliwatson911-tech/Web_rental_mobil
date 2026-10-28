@extends('layouts.app')
@section('title', 'Masuk Member')

@section('content')
<div class="mx-auto max-w-md py-6">
    <div class="card p-6 shadow-sm">
        <div class="text-center">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700 font-extrabold text-xl">P</span>
            <h1 class="mt-3 text-2xl font-extrabold text-slate-900">Masuk Member Padaidi</h1>
            <p class="mt-1 text-sm text-slate-500">Masuk untuk melihat pesanan, tier member, dan promo sewa.</p>
        </div>

        <form method="POST" action="{{ route('login.submit') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="label" for="email">Email atau Nomor WhatsApp</label>
                <input class="input" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@domain.com atau 08xxxxxxxxxx">
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label class="label" for="password">Kata Sandi</label>
                    <a href="{{ route('password.request') }}" class="text-xs text-brand-600 hover:underline">Lupa sandi?</a>
                </div>
                <input class="input" type="password" id="password" name="password" required placeholder="••••••••">
                @error('password')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="remember" value="1">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button class="btn btn-primary w-full py-2.5 font-bold">Masuk</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            Belum punya akun member? <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:underline">Daftar Member Baru</a>
        </p>

        <div class="mt-6 border-t border-slate-100 pt-4 text-center">
            <a href="{{ route('admin.login') }}" class="text-xs text-slate-400 hover:text-slate-600">Masuk sebagai Pengelola / Admin ↗</a>
        </div>
    </div>
</div>
@endsection

