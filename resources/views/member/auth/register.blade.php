@extends('layouts.app')
@section('title', 'Daftar Member - Padaidi Rent Car')

@section('content')
<div class="mx-auto max-w-md py-10 px-4">
    <div class="card card-accent-top p-8 shadow-sm border border-slate-200 bg-white relative">
        <div class="text-center">
            <a href="{{ route('home') }}" class="inline-block mb-3" title="Padaidi Rent Car">
                <img src="{{ asset('images/brand/padaidi-logo.png') }}" alt="Padaidi Rent Car" class="h-14 w-auto mx-auto object-contain">
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-wide uppercase">Daftar Member Baru</h1>
            <p class="mt-1 text-sm text-slate-500">Dapatkan diskon sewa otomatis, poin loyalitas, dan pemesanan instan.</p>
        </div>

        <form method="POST" action="{{ route('register.submit') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="label text-slate-700 font-bold" for="name">Nama Lengkap (sesuai KTP)</label>
                <input class="input" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Contoh: Budi Santoso">
                @error('name')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label text-slate-700 font-bold" for="email">Alamat Email</label>
                <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@domain.com">
                @error('email')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label text-slate-700 font-bold" for="phone">Nomor WhatsApp / HP Aktif</label>
                <input class="input" type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx">
                @error('phone')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label text-slate-700 font-bold" for="address">Alamat Domisili (Opsional)</label>
                <input class="input" id="address" name="address" value="{{ old('address') }}" placeholder="Kota Makassar">
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="label text-slate-700 font-bold" for="password">Kata Sandi</label>
                    <input class="input" type="password" id="password" name="password" required placeholder="Min. 6 karakter">
                    @error('password')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label text-slate-700 font-bold" for="password_confirmation">Ulangi Sandi</label>
                    <input class="input" type="password" id="password_confirmation" name="password_confirmation" required placeholder="Konfirmasi sandi">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full py-3 font-extrabold uppercase tracking-wider text-xs shadow-md">Daftar Sekarang</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            Sudah punya akun member? <a href="{{ route('login') }}" class="font-bold text-amber-600 hover:text-amber-800 hover:underline">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection
