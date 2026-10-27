@extends('layouts.app')
@section('title', 'Daftar Armada')

@section('content')
<h1 class="text-2xl font-bold text-slate-900">Daftar Armada</h1>
<div class="card mt-4 p-4">
    @include('partials.search-form', ['withCategory' => true])
    @if (! $hasDates && !empty($filters['category']))
        <p class="mt-2 text-xs text-slate-500">Isi tanggal untuk melihat mobil yang benar-benar tersedia.</p>
    @endif
</div>

<div class="mt-4 flex flex-wrap gap-2 text-sm">
    @php($base = array_filter(\Illuminate\Support\Arr::except($filters, 'category')))
    <a href="{{ route('cars.index', $base) }}" class="badge {{ empty($filters['category']) ? 'bg-brand-600 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-300' }} px-3 py-1">Semua</a>
    @foreach ($categories as $category)
        <a href="{{ route('cars.index', $base + ['category' => $category->slug]) }}" class="badge {{ ($filters['category'] ?? '') === $category->slug ? 'bg-brand-600 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-300' }} px-3 py-1">{{ $category->name }}</a>
    @endforeach
</div>

@if ($hasDates)
    <p class="mt-4 text-sm text-slate-600">Mobil tersedia untuk <strong>{{ tanggal($filters['pickup_date']) }}</strong> s.d. <strong>{{ tanggal($filters['return_date']) }}</strong> ({{ $days }} hari): <strong>{{ $cars->count() }}</strong> mobil.</p>
@endif

<div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($cars as $car)
        @include('partials.car-card', ['car' => $car])
    @empty
        <div class="card col-span-full p-8 text-center text-slate-600">
            Maaf, tidak ada mobil yang tersedia untuk pilihan ini. Coba ubah tanggal atau kategori, atau hubungi kami lewat WhatsApp.
        </div>
    @endforelse
</div>
@endsection
