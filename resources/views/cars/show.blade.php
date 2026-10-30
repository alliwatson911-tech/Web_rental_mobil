@extends('layouts.app')
@section('title', $car->name . ' - Padaidi Rent Car')

@section('content')
<x-breadcrumb :back-url="route('cars.index', array_filter($filters))" back-text="Kembali ke Daftar Armada" :crumbs="['Daftar Armada' => route('cars.index', array_filter($filters)), $car->name => null]" />

<div class="grid gap-8 lg:grid-cols-5 items-start">
    {{-- Kolom Kiri: Galeri Foto, Spesifikasi, dan Ulasan --}}
    <div class="lg:col-span-3 space-y-6">
        {{-- Foto Utama & Galeri --}}
        <div class="card overflow-hidden bg-white border border-slate-200 p-0 shadow-sm">
            @if ($car->photos->isNotEmpty())
                <img src="{{ $car->photos->first()->url() }}" alt="{{ $car->name }}" class="h-72 w-full object-cover md:h-96">
                @if ($car->photos->count() > 1)
                    <div class="grid grid-cols-4 gap-2 p-3 bg-slate-50 border-t border-slate-200">
                        @foreach ($car->photos->skip(1) as $photo)
                            <img src="{{ $photo->url() }}" alt="{{ $car->name }}" class="h-20 w-full rounded border border-slate-200 object-cover cursor-pointer hover:border-[#F5A623] transition-colors" loading="lazy">
                        @endforeach
                    </div>
                @endif
            @else
                @include('partials.car-image', ['car' => $car->setRelation('primaryPhoto', null), 'class' => 'h-72 w-full md:h-96'])
            @endif
        </div>

        {{-- Spesifikasi Armada --}}
        <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
            <div class="mb-4">
                <span class="section-kicker">INFORMASI LENGKAP</span>
                <h2 class="text-xl font-black uppercase text-slate-900 tracking-wide">Spesifikasi Kendaraan</h2>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-xs md:grid-cols-3 pt-2 border-t border-slate-200">
                <div class="bg-slate-50 p-3 rounded border border-slate-200">
                    <dt class="text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Kategori</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1">{{ $car->category->name }}</dd>
                </div>
                <div class="bg-slate-50 p-3 rounded border border-slate-200">
                    <dt class="text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Merek</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1">{{ $car->brand }}</dd>
                </div>
                <div class="bg-slate-50 p-3 rounded border border-slate-200">
                    <dt class="text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Tahun Produksi</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1">{{ $car->year ?? '-' }}</dd>
                </div>
                <div class="bg-slate-50 p-3 rounded border border-slate-200">
                    <dt class="text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Transmisi</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1">{{ $car->transmissionLabel() }}</dd>
                </div>
                <div class="bg-slate-50 p-3 rounded border border-slate-200">
                    <dt class="text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Kapasitas Kursi</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1">{{ $car->seats }} Penumpang</dd>
                </div>
                <div class="bg-slate-50 p-3 rounded border border-slate-200">
                    <dt class="text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Bahan Bakar</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1">{{ $car->fuel }}</dd>
                </div>
            </dl>

            @if ($car->description)
                <div class="mt-6 pt-5 border-t border-slate-200">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Deskripsi &amp; Fasilitas:</h3>
                    <p class="whitespace-pre-line text-xs text-slate-600 leading-relaxed">{{ $car->description }}</p>
                </div>
            @endif
        </div>

        {{-- Ulasan Pelanggan --}}
        @if ($car->reviews->isNotEmpty())
            <div class="card p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
                <div class="mb-4">
                    <span class="section-kicker">TESTIMONI</span>
                    <h2 class="text-xl font-black uppercase text-slate-900 tracking-wide">Ulasan Pelanggan</h2>
                </div>
                <div class="space-y-4 pt-2 border-t border-slate-200">
                    @foreach ($car->reviews as $review)
                        <div class="bg-slate-50 p-4 rounded border border-slate-200 text-xs">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-extrabold text-slate-900 uppercase">{{ $review->customer_name }}</span>
                                <span class="text-[#F5A623] text-sm tracking-widest">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                            </div>
                            @if ($review->comment)
                                <p class="text-slate-600 leading-relaxed italic">"{{ $review->comment }}"</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Kolom Kanan: Kotak Pemesanan Sticky --}}
    <aside class="lg:col-span-2">
        <div class="card card-accent-top sticky top-24 p-6 md:p-8 bg-white border border-slate-200 shadow-xl" style="position: sticky; top: 6rem; z-index: 20;">
            <span class="badge badge-accent mb-3">{{ $car->category->name }}</span>
            <h1 class="text-2xl md:text-3xl font-black uppercase text-slate-900 tracking-wide leading-tight">{{ $car->name }}</h1>
            
            <div class="mt-3 pt-3 border-t border-slate-200">
                <span class="text-[11px] uppercase tracking-wider text-slate-500 block font-bold">Tarif Sewa Harian:</span>
                <p class="text-2xl font-black text-amber-700 mt-0.5">
                    {{ rupiah($car->daily_price) }} <span class="text-xs font-normal text-slate-500">/ hari</span>
                </p>
            </div>

            @if ($car->status !== \App\Models\Car::STATUS_AVAILABLE)
                <div class="mt-5 rounded border border-amber-300 bg-amber-50 p-3.5 text-xs text-amber-900">
                    Mobil ini sedang {{ strtolower($car->statusLabel()) }} dan belum bisa dipesan.
                </div>
            @else
                @if ($available === true)
                    <div class="mt-5 rounded border border-emerald-300 bg-emerald-50 p-3.5 text-xs text-emerald-900 font-semibold flex items-center gap-2">
                        <x-icon name="check" class="w-4 h-4 text-emerald-600 shrink-0" />
                        <span>Tersedia untuk {{ tanggal($filters['pickup_date']) }} s.d. {{ tanggal($filters['return_date']) }}.</span>
                    </div>
                @elseif ($available === false)
                    <div class="mt-5 rounded border border-rose-300 bg-rose-50 p-3.5 text-xs text-rose-900 font-semibold flex items-center gap-2">
                        <x-icon name="cross" class="w-4 h-4 text-rose-600 shrink-0" />
                        <span>Tidak tersedia pada tanggal tersebut. Silakan pilih tanggal lain.</span>
                    </div>
                @endif

                <form method="GET" action="{{ route('bookings.create', $car) }}" class="mt-6 space-y-4">
                    <div>
                        <label class="label text-slate-700 font-bold" for="pickup_location_id">Lokasi ambil</label>
                        <select class="input" id="pickup_location_id" name="pickup_location_id">
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected(($filters['pickup_location_id'] ?? null) == $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label text-slate-700 font-bold" for="pickup_date">Tanggal ambil</label>
                            <input class="input" type="date" id="pickup_date" name="pickup_date" min="{{ now()->toDateString() }}" value="{{ $filters['pickup_date'] ?? '' }}" required>
                        </div>
                        <div>
                            <label class="label text-slate-700 font-bold" for="return_date">Tanggal kembali</label>
                            <input class="input" type="date" id="return_date" name="return_date" min="{{ now()->addDay()->toDateString() }}" value="{{ $filters['return_date'] ?? '' }}" required>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary w-full py-3.5 font-black uppercase text-xs tracking-wider">
                            Lanjut ke Pengisian Pesanan →
                        </button>
                    </div>
                </form>
            @endif

            <div class="mt-6 border-t border-slate-200 pt-4 text-[11px] text-slate-500 space-y-2">
                <p class="flex items-center gap-2">
                    <x-icon name="shield" class="w-4 h-4 text-[#F5A623] shrink-0" />
                    <span>Unit berasuransi &amp; kondisi prima terawat</span>
                </p>
                <p class="flex items-center gap-2">
                    <x-icon name="clock" class="w-4 h-4 text-[#F5A623] shrink-0" />
                    <span>Layanan antar jemput bandara 24 jam nonstop</span>
                </p>
            </div>
        </div>
    </aside>
</div>
@endsection
