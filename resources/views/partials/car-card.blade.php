@php($query = array_filter(['pickup_location_id' => $filters['pickup_location_id'] ?? null, 'pickup_date' => $filters['pickup_date'] ?? null, 'return_date' => $filters['return_date'] ?? null]))
<article class="card card-hover flex flex-col overflow-hidden border border-slate-200 bg-white rounded-lg transition-all group shadow-sm hover:shadow-md">
    <a href="{{ route('cars.show', ['car' => $car] + $query) }}" class="relative block overflow-hidden">
        @include('partials.car-image', ['car' => $car, 'class' => 'h-48 w-full object-cover transition-transform duration-300 group-hover:scale-105'])
        <div class="absolute top-3 left-3 z-10">
            <span class="badge badge-accent shadow-sm">{{ $car->category->name }}</span>
        </div>
        @if ($car->status !== \App\Models\Car::STATUS_AVAILABLE)
            <div class="absolute top-3 right-3 z-10">
                <span class="badge badge-warning shadow-sm">{{ $car->statusLabel() }}</span>
            </div>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h3 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">
                    <a href="{{ route('cars.show', ['car' => $car] + $query) }}" class="hover:text-[#F5A623] transition-colors">{{ $car->name }}</a>
                </h3>
                <p class="mt-1.5 text-xs text-slate-500 flex items-center gap-2">
                    <span class="font-semibold text-slate-700">{{ $car->transmissionLabel() }}</span>
                    <span class="text-slate-300">•</span>
                    <span>{{ $car->seats }} Kursi</span>
                    <span class="text-slate-300">•</span>
                    <span>{{ $car->fuel }}</span>
                </p>
            </div>
        </div>
        
        <div class="mt-4 pt-3.5 border-t border-slate-200 flex items-baseline justify-between">
            <div>
                <span class="text-[11px] uppercase tracking-wider text-slate-500 block font-bold">Tarif Sewa</span>
                <p class="text-xl font-black text-[#F5A623] leading-tight">{{ rupiah($car->daily_price) }} <span class="text-xs font-normal text-slate-500">/ hari</span></p>
            </div>
            @if (!empty($days))
                <div class="text-right">
                    <span class="text-[11px] text-slate-500 block font-medium">Estimasi {{ $days }} hari:</span>
                    <span class="text-xs font-extrabold text-slate-900">{{ rupiah($car->daily_price * $days) }}</span>
                </div>
            @endif
        </div>

        <div class="mt-5 grid grid-cols-2 gap-2 pt-1">
            <a href="{{ route('cars.show', ['car' => $car] + $query) }}" class="btn btn-outline text-center text-xs py-2 uppercase font-bold tracking-wider border-slate-300 text-slate-700 hover:border-[#F5A623] hover:text-[#F5A623]">Detail</a>
            @if ($car->status === \App\Models\Car::STATUS_AVAILABLE)
                <a href="{{ route('bookings.create', ['car' => $car] + $query) }}" class="btn btn-primary text-center text-xs py-2 uppercase font-black tracking-wider">Pesan</a>
            @else
                <button disabled class="btn btn-outline opacity-50 cursor-not-allowed text-center text-xs py-2 uppercase border-slate-200 text-slate-400">Penuh</button>
            @endif
        </div>
    </div>
</article>
