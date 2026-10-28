@extends('layouts.app')
@section('title', 'Lupa Kata Sandi')

@section('content')
<div class="mx-auto max-w-md py-6">
    <div class="card p-6 shadow-sm">
        <h1 class="text-xl font-bold text-slate-900">Pemulihan Kata Sandi</h1>
        <p class="mt-1 text-sm text-slate-500">Masukkan alamat email Anda untuk menerima tautan pemulihan kata sandi.</p>

        <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label class="label" for="email">Alamat Email Terdaftar</label>
                <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@domain.com">
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>

            <button class="btn btn-primary w-full py-2.5 font-bold">Kirim Tautan Pemulihan</button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-brand-600 hover:underline">← Kembali ke halaman Masuk</a>
        </div>
    </div>
</div>
@endsection

