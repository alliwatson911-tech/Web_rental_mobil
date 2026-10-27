@extends('layouts.app')
@section('title', 'Cek Pesanan')

@section('content')
<div class="mx-auto max-w-md">
    <h1 class="text-2xl font-bold text-slate-900">Cek status pesanan</h1>
    <p class="mt-1 text-sm text-slate-500">Masukkan kode pesanan (contoh: PDD-AB12CD) dan nomor HP yang dipakai saat memesan.</p>
    <form method="POST" action="{{ route('bookings.lookup.submit') }}" class="card mt-4 space-y-3 p-5">
        @csrf
        <div>
            <label class="label" for="code">Kode pesanan</label>
            <input class="input uppercase" id="code" name="code" value="{{ old('code', $code) }}" required>
        </div>
        <div>
            <label class="label" for="customer_phone">Nomor HP</label>
            <input class="input" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required inputmode="tel">
        </div>
        <button class="btn btn-primary w-full">Lihat pesanan</button>
    </form>
</div>
@endsection
