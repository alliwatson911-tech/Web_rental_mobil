@php($query = array_filter(['pickup_location_id' => $filters['pickup_location_id'] ?? null, 'pickup_date' => $filters['pickup_date'] ?? null, 'return_date' => $filters['return_date'] ?? null]))
<article class="card flex flex-col overflow-hidden">
    <a href="{{ route('cars.show', ['car' => $car] + $query) }}">
        @include('partials.car-image', ['car' => $car])
    </a>
    <div class="flex flex-1 flex-col p-4">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h3 class="font-bold text-slate-900"><a href="{{ route('cars.show', ['car' => $car] + $query) }}" class="hover:text-brand-600">{{ $car->name }}</a></h3>
                <p class="text-xs text-slate-500">{{ $car->category->name }} · {{ $car->transmissionLabel() }} · {{ $car->seats }} kursi</p>
            </div>
            @if ($car->status !== \App\Models\Car::STATUS_AVAILABLE)
                <span class="badge bg-amber-100 text-amber-800">{{ $car->statusLabel() }}</span>
            @endif
        </div>
        <p class="mt-3 text-lg font-extrabold text-brand-700">{{ rupiah($car->daily_price) }} <span class="text-xs font-medium text-slate-500">/ hari</span></p>
        @if (!empty($days))
            <p class="text-xs text-slate-500">Estimasi {{ $days }} hari: <strong>{{ rupiah($car->daily_price * $days) }}</strong> (belum termasuk tambahan)</p>
        @endif
        <div class="mt-4 flex gap-2 pt-1">
            <a href="{{ route('cars.show', ['car' => $car] + $query) }}" class="btn btn-outline flex-1">Detail</a>
            @if ($car->status === \App\Models\Car::STATUS_AVAILABLE)
                <a href="{{ route('bookings.create', ['car' => $car] + $query) }}" class="btn btn-primary flex-1">Pesan</a>
            @endif
        </div>
    </div>
</article>
