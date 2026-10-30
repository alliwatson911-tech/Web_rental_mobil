@extends('layouts.app')
@section('title', 'Daftar Armada - Padaidi Rent Car')

@section('content')
<x-breadcrumb :crumbs="['Daftar Armada' => null]" />

<div class="mb-6">
    <span class="section-kicker">KATALOG LENGKAP</span>
    <h1 class="section-title text-slate-900 mb-2">Daftar Armada</h1>
    <p class="text-slate-600 text-sm max-w-2xl">Pilihan mobil terawat untuk sewa lepas kunci maupun dengan sopir di Makassar. Sesuaikan tanggal perjalanan untuk memeriksa ketersediaan langsung.</p>
</div>

{{-- Kotak Pencarian & Filter --}}
<div class="card card-accent-top p-6 bg-white border border-slate-200 shadow-sm">
    <div class="mb-4">
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
            <x-icon name="calendar" class="w-4 h-4 text-[#F5A623]" />
            <span>Atur Lokasi &amp; Tanggal Sewa</span>
        </h2>
    </div>
    @include('partials.search-form', ['withCategory' => true])
    @if (! $hasDates && !empty($filters['category']))
        <p class="mt-3 text-xs text-amber-700 font-medium">ℹ️ Isi tanggal ambil dan kembali untuk melihat unit yang benar-benar tersedia pada periode Anda.</p>
    @endif
</div>

{{-- Bar Filter Cepat (Kategori, Transmisi, Kapasitas Kursi) --}}
<div class="mt-6 flex flex-col gap-4 bg-slate-50 p-5 rounded-lg border border-slate-200">
    <!-- Filter Kategori -->
    <div class="flex flex-wrap items-center gap-2 text-xs">
        <span class="font-extrabold uppercase tracking-wider text-slate-600 w-24">Kategori:</span>
        @php($baseCat = array_filter(\Illuminate\Support\Arr::except($filters, 'category')))
        <a href="{{ route('cars.index', $baseCat) }}" class="badge {{ empty($filters['category']) ? 'badge-accent' : 'bg-white text-slate-700 border border-slate-300 hover:border-[#F5A623] hover:text-[#F5A623]' }} px-3 py-1.5 transition-colors">Semua Kategori</a>
        @foreach ($categories as $category)
            <a href="{{ route('cars.index', $baseCat + ['category' => $category->slug]) }}" class="badge {{ ($filters['category'] ?? '') === $category->slug ? 'badge-accent' : 'bg-white text-slate-700 border border-slate-300 hover:border-[#F5A623] hover:text-[#F5A623]' }} px-3 py-1.5 transition-colors">{{ $category->name }}</a>
        @endforeach
    </div>

    <!-- Filter Transmisi & Kapasitas Kursi -->
    <div class="flex flex-wrap items-center gap-6 pt-3 border-t border-slate-200 text-xs">
        <div class="flex flex-wrap items-center gap-2">
            <span class="font-extrabold uppercase tracking-wider text-slate-600 w-24">Transmisi:</span>
            @php($baseTrans = array_filter(\Illuminate\Support\Arr::except($filters, 'transmission')))
            <a href="{{ route('cars.index', $baseTrans) }}" class="badge {{ empty($filters['transmission']) ? 'badge-accent' : 'bg-white text-slate-700 border border-slate-300 hover:border-[#F5A623] hover:text-[#F5A623]' }} px-2.5 py-1">Semua</a>
            <a href="{{ route('cars.index', $baseTrans + ['transmission' => 'automatic']) }}" class="badge {{ ($filters['transmission'] ?? '') === 'automatic' ? 'badge-accent' : 'bg-white text-slate-700 border border-slate-300 hover:border-[#F5A623] hover:text-[#F5A623]' }} px-2.5 py-1">Matic (Otomatis)</a>
            <a href="{{ route('cars.index', $baseTrans + ['transmission' => 'manual']) }}" class="badge {{ ($filters['transmission'] ?? '') === 'manual' ? 'badge-accent' : 'bg-white text-slate-700 border border-slate-300 hover:border-[#F5A623] hover:text-[#F5A623]' }} px-2.5 py-1">Manual</a>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="font-extrabold uppercase tracking-wider text-slate-600">Kapasitas:</span>
            @php($baseSeats = array_filter(\Illuminate\Support\Arr::except($filters, 'seats')))
            <a href="{{ route('cars.index', $baseSeats) }}" class="badge {{ empty($filters['seats']) ? 'badge-accent' : 'bg-white text-slate-700 border border-slate-300 hover:border-[#F5A623] hover:text-[#F5A623]' }} px-2.5 py-1">Semua</a>
            <a href="{{ route('cars.index', $baseSeats + ['seats' => 5]) }}" class="badge {{ ($filters['seats'] ?? '') == 5 ? 'badge-accent' : 'bg-white text-slate-700 border border-slate-300 hover:border-[#F5A623] hover:text-[#F5A623]' }} px-2.5 py-1">≥ 5 Kursi</a>
            <a href="{{ route('cars.index', $baseSeats + ['seats' => 7]) }}" class="badge {{ ($filters['seats'] ?? '') == 7 ? 'badge-accent' : 'bg-white text-slate-700 border border-slate-300 hover:border-[#F5A623] hover:text-[#F5A623]' }} px-2.5 py-1">≥ 7 Kursi</a>
        </div>
    </div>
</div>

@if ($hasDates)
    <div class="mt-6 p-4 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-center justify-between">
        <div>
            Mobil tersedia untuk <strong>{{ tanggal($filters['pickup_date']) }}</strong> s.d. <strong>{{ tanggal($filters['return_date']) }}</strong> ({{ $days }} hari): <strong class="text-amber-700 text-sm">{{ $cars->count() }}</strong> mobil.
        </div>
        <a href="{{ route('cars.index') }}" class="underline text-amber-800 hover:text-amber-950">Reset tanggal</a>
    </div>
@endif

<div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($cars as $car)
        @include('partials.car-card', ['car' => $car, 'filters' => $filters, 'days' => $days])
    @empty
        <div class="col-span-full card p-12 text-center bg-white border border-slate-200">
            <div class="mx-auto h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                <x-icon name="cross" class="w-6 h-6" />
            </div>
            <p class="font-extrabold text-base text-slate-800 uppercase">Tidak ada mobil yang sesuai filter</p>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Silakan ubah tanggal sewa atau reset filter kategori/transmisi untuk menemukan armada lainnya.</p>
            <div class="mt-4">
                <a href="{{ route('cars.index') }}" class="btn btn-outline text-xs uppercase py-2 px-4 border-slate-300 text-slate-700">Lihat Semua Mobil</a>
            </div>
        </div>
    @endforelse
</div>
@endsection
