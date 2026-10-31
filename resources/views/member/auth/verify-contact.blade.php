@extends('layouts.app')
@section('title', 'Verifikasi Kontak Member - Padaidi Rent Car')

@section('content')
<div class="mx-auto max-w-md py-6 px-4">
    <x-breadcrumb :back-url="route('member.account')" back-text="Kembali ke Dashboard Member" :crumbs="['Dashboard' => route('member.account'), 'Verifikasi Kontak' => null]" />

    <div class="card card-accent-top p-8 shadow-sm border border-slate-200 bg-white relative">
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-block mb-3" title="Padaidi Rent Car">
                <img src="{{ asset('images/brand/padaidi-logo.png') }}" alt="Padaidi Rent Car" class="h-14 w-auto mx-auto object-contain">
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-wide uppercase">Verifikasi WhatsApp</h1>
            <p class="mt-1 text-sm text-slate-500">Pastikan nomor WhatsApp Anda aktif untuk menerima bukti sewa dan kontak sopir.</p>
        </div>

        <div class="mb-4 rounded border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">
            Nomor HP terdaftar: <strong class="text-amber-800">{{ $user->phone }}</strong>
        </div>

        <form method="POST" action="{{ route('member.verify.wa') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label text-slate-700 font-bold" for="otp">Kode OTP (6 Digit)</label>
                <input class="input text-center tracking-widest text-lg font-bold" id="otp" name="otp" maxlength="6" placeholder="123456" required autofocus>
                <p class="mt-1 text-xs text-slate-500">Gunakan 6 digit contoh (misal: <code>123456</code>) untuk verifikasi kontak nomor Anda.</p>
                @error('otp')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="btn btn-primary w-full py-3 font-extrabold uppercase tracking-wider text-xs shadow-md">Verifikasi Sekarang</button>
        </form>

        <div class="mt-6 text-center border-t border-slate-200 pt-4 text-xs text-slate-500">
            Ingin verifikasi nanti? <a href="{{ route('member.account') }}" class="font-bold text-amber-600 hover:text-amber-800 hover:underline">Lewati ke Dashboard</a>
        </div>
    </div>
</div>
@endsection
