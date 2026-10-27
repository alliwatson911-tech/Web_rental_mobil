@extends('layouts.app')

@section('hero')
<section class="relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-700 to-brand-500 text-white">
    <div class="mx-auto max-w-6xl px-4 pb-24 pt-12 md:pt-20">
        <p class="badge bg-white/15 text-white">{{ $business['hours'] }} · Makassar</p>
        <h1 class="mt-4 max-w-2xl text-3xl font-extrabold leading-tight md:text-5xl">Sewa mobil di Makassar, mudah &amp; jelas harganya.</h1>
        <p class="mt-3 max-w-xl text-white/80">Lepas kunci atau dengan sopir. Ambil di kantor, bandara, atau kami antar ke alamat Anda.</p>
    </div>
</section>
<div class="relative z-10 mx-auto -mt-16 max-w-6xl px-4">
    <div class="card p-4 md:p-6">
        <h2 class="mb-3 text-lg font-bold text-slate-900">Cari mobil yang tersedia</h2>
        @include('partials.search-form', ['filters' => ['pickup_date' => now()->addDay()->toDateString(), 'return_date' => now()->addDays(3)->toDateString()]])
    </div>
</div>
@endsection

@section('content')
<section class="mt-4">
    <h2 class="text-xl font-bold text-slate-900">Kategori armada</h2>
    <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach ($categories as $category)
            <a href="{{ route('cars.index', ['category' => $category->slug]) }}" class="card p-4 transition hover:border-brand-500">
                <p class="font-semibold text-slate-900">{{ $category->name }}</p>
                <p class="text-xs text-slate-500">{{ $category->cars_count }} mobil</p>
            </a>
        @endforeach
    </div>
</section>

<section class="mt-10">
    <div class="flex items-end justify-between">
        <h2 class="text-xl font-bold text-slate-900">Pilihan mobil</h2>
        <a href="{{ route('cars.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">Lihat semua →</a>
    </div>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($featuredCars as $car)
            @include('partials.car-card', ['car' => $car, 'filters' => []])
        @endforeach
    </div>
</section>

<section class="mt-10 grid gap-4 md:grid-cols-3">
    <div class="card p-5"><p class="text-2xl">🔑</p><p class="mt-2 font-bold">Lepas kunci</p><p class="text-sm text-slate-600">Bawa sendiri mobilnya. Cukup KTP &amp; SIM A yang masih berlaku.</p></div>
    <div class="card p-5"><p class="text-2xl">🧑‍✈️</p><p class="mt-2 font-bold">Tambah sopir</p><p class="text-sm text-slate-600">Pilih tambahan sopir per hari saat memesan.</p></div>
    <div class="card p-5"><p class="text-2xl">🏦</p><p class="mt-2 font-bold">Transfer manual</p><p class="text-sm text-slate-600">Bayar via transfer bank lalu unggah bukti di halaman status pesanan.</p></div>
</section>
@endsection
