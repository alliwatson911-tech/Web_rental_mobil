@extends('layouts.app')
@section('title', 'Atur Ulang Kata Sandi - Padaidi Rent Car')

@section('content')
<div class="mx-auto max-w-md py-6 px-4">
    <x-breadcrumb :back-url="route('login')" back-text="Kembali ke Masuk Member" :crumbs="['Masuk Member' => route('login'), 'Atur Ulang Sandi' => null]" />

    <div class="card card-accent-top p-8 shadow-sm border border-slate-200 bg-white relative">
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-block mb-3" title="Padaidi Rent Car">
                <img src="{{ asset('images/brand/padaidi-logo.png') }}" alt="Padaidi Rent Car" class="h-14 w-auto mx-auto object-contain">
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-wide uppercase">Atur Ulang Sandi</h1>
            <p class="mt-1 text-sm text-slate-500">Tentukan kata sandi baru untuk akun member Anda.</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label class="label text-slate-700 font-bold" for="email">Alamat Email</label>
                <input class="input" type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus>
                @error('email')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label text-slate-700 font-bold" for="password">Kata Sandi Baru</label>
                <input class="input" type="password" id="password" name="password" required placeholder="Min. 6 karakter">
                @error('password')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label text-slate-700 font-bold" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                <input class="input" type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi">
            </div>

            <button type="submit" class="btn btn-primary w-full py-3 font-extrabold uppercase tracking-wider text-xs shadow-md">Simpan Kata Sandi Baru</button>
        </form>
    </div>
</div>
@endsection
