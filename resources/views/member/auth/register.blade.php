@extends('layouts.app')
@section('title', 'Daftar Member')

@section('content')
<div class="mx-auto max-w-md py-6">
    <div class="card p-6 shadow-sm">
        <div class="text-center">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700 font-extrabold text-xl">M</span>
            <h1 class="mt-3 text-2xl font-extrabold text-slate-900">Daftar Member Padaidi</h1>
            <p class="mt-1 text-sm text-slate-500">Dapatkan diskon sewa mobil, poin loyalitas, dan pemesanan instan.</p>
        </div>

        <form method="POST" action="{{ route('register.submit') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="label" for="name">Nama Lengkap (sesuai KTP)</label>
                <input class="input" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Contoh: Budi Santoso">
                @error('name')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label" for="email">Alamat Email</label>
                <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@domain.com">
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label" for="phone">Nomor WhatsApp / HP Aktif</label>
                <input class="input" type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx">
                @error('phone')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label" for="address">Alamat Domisili (Opsional)</label>
                <input class="input" id="address" name="address" value="{{ old('address') }}" placeholder="Kota Makassar">
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="label" for="password">Kata Sandi</label>
                    <input class="input" type="password" id="password" name="password" required placeholder="Min. 6 karakter">
                    @error('password')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="password_confirmation">Ulangi Kata Sandi</label>
                    <input class="input" type="password" id="password_confirmation" name="password_confirmation" required placeholder="Konfirmasi sandi">
                </div>
            </div>

            <button class="btn btn-primary w-full py-2.5 font-bold">Daftar Sekarang</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            Sudah punya akun member? <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection

