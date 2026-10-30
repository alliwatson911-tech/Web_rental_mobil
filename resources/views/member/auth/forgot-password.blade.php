@extends('layouts.app')
@section('title', 'Lupa Kata Sandi - Padaidi Rent Car')

@section('content')
<div class="mx-auto max-w-md py-6 px-4">
    <x-breadcrumb :back-url="route('login')" back-text="Kembali ke Masuk Member" :crumbs="['Masuk Member' => route('login'), 'Lupa Sandi' => null]" />

    <div class="card card-accent-top p-8 shadow-sm border border-slate-200 bg-white relative">
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-block mb-3" title="Padaidi Rent Car">
                <img src="{{ asset('images/brand/padaidi-logo.png') }}" alt="Padaidi Rent Car" class="h-14 w-auto mx-auto object-contain">
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-wide uppercase">Pemulihan Sandi</h1>
            <p class="mt-1 text-sm text-slate-500">Masukkan alamat email Anda untuk menerima tautan pemulihan kata sandi.</p>
        </div>

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label text-slate-700 font-bold" for="email">Alamat Email Terdaftar</label>
                <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@domain.com">
                @error('email')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="btn btn-primary w-full py-3 font-extrabold uppercase tracking-wider text-xs shadow-md">Kirim Tautan Pemulihan</button>
        </form>

        <div class="mt-6 text-center border-t border-slate-200 pt-4">
            <a href="{{ route('login') }}" class="text-sm font-bold text-amber-600 hover:text-amber-800 hover:underline">← Kembali ke Halaman Masuk</a>
        </div>
    </div>
</div>
@endsection
