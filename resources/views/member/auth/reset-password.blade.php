@extends('layouts.app')
@section('title', 'Atur Ulang Kata Sandi')

@section('content')
<div class="mx-auto max-w-md py-6">
    <div class="card p-6 shadow-sm">
        <h1 class="text-xl font-bold text-slate-900">Atur Ulang Kata Sandi</h1>
        <p class="mt-1 text-sm text-slate-500">Tentukan kata sandi baru untuk akun member Anda.</p>

        <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label class="label" for="email">Alamat Email</label>
                <input class="input" type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus>
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label" for="password">Kata Sandi Baru</label>
                <input class="input" type="password" id="password" name="password" required placeholder="Min. 6 karakter">
                @error('password')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                <input class="input" type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi">
            </div>

            <button class="btn btn-primary w-full py-2.5 font-bold">Simpan Kata Sandi Baru</button>
        </form>
    </div>
</div>
@endsection

