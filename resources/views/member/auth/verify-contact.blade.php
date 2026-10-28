@extends('layouts.app')
@section('title', 'Verifikasi Kontak Member')

@section('content')
<div class="mx-auto max-w-md py-6">
    <div class="card p-6 shadow-sm">
        <h1 class="text-xl font-bold text-slate-900">Verifikasi Nomor WhatsApp</h1>
        <p class="mt-1 text-sm text-slate-500">Pastikan nomor WhatsApp Anda aktif untuk menerima bukti sewa, kontak sopir, dan status pesanan.</p>

        <div class="mt-4 rounded-lg bg-blue-50 border border-blue-200 p-3 text-sm text-blue-900">
            Nomor HP terdaftar: <strong>{{ $user->phone }}</strong>
        </div>

        <form method="POST" action="{{ route('member.verify.wa') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label class="label" for="otp">Kode OTP (6 Digit)</label>
                <input class="input text-center tracking-widest text-lg font-bold" id="otp" name="otp" maxlength="6" placeholder="123456" required autofocus>
                <p class="mt-1 text-xs text-slate-500">Gunakan 6 digit contoh (misal: <code>123456</code>) untuk menyelesaikan verifikasi kontak nomor Anda.</p>
                @error('otp')<p class="error">{{ $message }}</p>@enderror
            </div>

            <button class="btn btn-primary w-full py-2.5 font-bold">Verifikasi Sekarang</button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('member.account') }}" class="text-sm font-semibold text-brand-600 hover:underline">← Kembali ke Akun Saya</a>
        </div>
    </div>
</div>
@endsection

