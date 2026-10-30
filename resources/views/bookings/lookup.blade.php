@extends('layouts.app')
@section('title', 'Cek Pesanan - Padaidi Rent Car')

@section('content')
<div class="mx-auto max-w-md py-4">
    <x-breadcrumb :crumbs="['Cek Pesanan' => null]" />

    <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
        <div class="mb-5 text-center">
            <span class="section-kicker mb-1">LACAK PESANAN</span>
            <h1 class="text-xl md:text-2xl font-black uppercase text-slate-900 tracking-wide">Cek status pesanan</h1>
            <p class="mt-1.5 text-xs text-slate-500 leading-relaxed">
                Masukkan kode pesanan (contoh: <code>PDD-AB12CD</code>) dan nomor HP/WhatsApp yang digunakan saat memesan.
            </p>
        </div>

        <form method="POST" action="{{ route('bookings.lookup.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label text-slate-700 font-bold" for="code">Kode pesanan</label>
                <input class="input font-mono uppercase tracking-widest text-center text-base font-bold" id="code" name="code" value="{{ old('code', $code) }}" required placeholder="PDD-XXXXXX" autofocus>
                @error('code')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label text-slate-700 font-bold" for="customer_phone">Nomor HP</label>
                <input class="input" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required inputmode="tel" placeholder="08xxxxxxxxxx">
                @error('customer_phone')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn btn-primary w-full py-3.5 font-black uppercase tracking-wider text-xs shadow-md">
                Lihat pesanan
            </button>
        </form>
    </div>
</div>
@endsection
