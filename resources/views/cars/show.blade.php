@extends('layouts.app')
@section('title', $car->name)

@section('content')
<a href="{{ route('cars.index', array_filter($filters)) }}" class="text-sm text-brand-600 hover:underline">← Kembali ke daftar armada</a>
<div class="mt-3 grid gap-6 lg:grid-cols-5">
    <div class="lg:col-span-3">
        <div class="card overflow-hidden">
            @if ($car->photos->isNotEmpty())
                <img src="{{ $car->photos->first()->url() }}" alt="{{ $car->name }}" class="h-64 w-full object-cover md:h-96">
                @if ($car->photos->count() > 1)
                    <div class="grid grid-cols-4 gap-2 p-2">
                        @foreach ($car->photos->skip(1) as $photo)
                            <img src="{{ $photo->url() }}" alt="{{ $car->name }}" class="h-20 w-full rounded object-cover" loading="lazy">
                        @endforeach
                    </div>
                @endif
            @else
                @include('partials.car-image', ['car' => $car->setRelation('primaryPhoto', null), 'class' => 'h-64 w-full md:h-96'])
            @endif
        </div>
        <div class="card mt-4 p-5">
            <h2 class="font-bold text-slate-900">Spesifikasi</h2>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm md:grid-cols-3">
                <div><dt class="text-slate-500">Kategori</dt><dd class="font-medium">{{ $car->category->name }}</dd></div>
                <div><dt class="text-slate-500">Merek</dt><dd class="font-medium">{{ $car->brand }}</dd></div>
                <div><dt class="text-slate-500">Tahun</dt><dd class="font-medium">{{ $car->year ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Transmisi</dt><dd class="font-medium">{{ $car->transmissionLabel() }}</dd></div>
                <div><dt class="text-slate-500">Kursi</dt><dd class="font-medium">{{ $car->seats }} orang</dd></div>
                <div><dt class="text-slate-500">Bahan bakar</dt><dd class="font-medium">{{ $car->fuel }}</dd></div>
            </dl>
            @if ($car->description)
                <p class="mt-4 whitespace-pre-line text-sm text-slate-700">{{ $car->description }}</p>
            @endif
        </div>
        @if ($car->reviews->isNotEmpty())
            <div class="card mt-4 p-5">
                <h2 class="font-bold text-slate-900">Ulasan pelanggan</h2>
                @foreach ($car->reviews as $review)
                    <div class="mt-3 border-t border-slate-100 pt-3 text-sm">
                        <p class="font-semibold">{{ $review->customer_name }} <span class="text-accent-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span></p>
                        @if ($review->comment)<p class="text-slate-600">{{ $review->comment }}</p>@endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <aside class="lg:col-span-2">
        <div class="card sticky top-20 p-5">
            <h1 class="text-2xl font-extrabold text-slate-900">{{ $car->name }}</h1>
            <p class="mt-1 text-2xl font-extrabold text-brand-700">{{ rupiah($car->daily_price) }} <span class="text-sm font-medium text-slate-500">/ hari</span></p>

            @if ($car->status !== \App\Models\Car::STATUS_AVAILABLE)
                <p class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">Mobil ini sedang {{ strtolower($car->statusLabel()) }} dan belum bisa dipesan.</p>
            @else
                @if ($available === true)
                    <p class="mt-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">✔ Tersedia untuk {{ tanggal($filters['pickup_date']) }} s.d. {{ tanggal($filters['return_date']) }}.</p>
                @elseif ($available === false)
                    <p class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">✖ Tidak tersedia pada tanggal tersebut. Silakan pilih tanggal lain.</p>
                @endif
                <form method="GET" action="{{ route('bookings.create', $car) }}" class="mt-4 space-y-3">
                    <div>
                        <label class="label" for="pickup_location_id">Lokasi ambil</label>
                        <select class="input" id="pickup_location_id" name="pickup_location_id">
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected(($filters['pickup_location_id'] ?? null) == $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label" for="pickup_date">Tanggal ambil</label>
                            <input class="input" type="date" id="pickup_date" name="pickup_date" min="{{ now()->toDateString() }}" value="{{ $filters['pickup_date'] ?? '' }}" required>
                        </div>
                        <div>
                            <label class="label" for="return_date">Tanggal kembali</label>
                            <input class="input" type="date" id="return_date" name="return_date" min="{{ now()->addDay()->toDateString() }}" value="{{ $filters['return_date'] ?? '' }}" required>
                        </div>
                    </div>
                    <button class="btn btn-primary w-full py-3">Pesan mobil ini</button>
                </form>
            @endif
            <p class="mt-4 text-xs text-slate-500">Harga belum termasuk tambahan (sopir, asuransi, antar-jemput). Lihat <a class="text-brand-600 underline" href="{{ route('terms') }}">syarat sewa</a>.</p>
        </div>
    </aside>
</div>
@endsection
