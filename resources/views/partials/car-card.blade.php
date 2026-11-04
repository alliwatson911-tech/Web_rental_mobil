@php($query = array_filter(['pickup_location_id' => $filters['pickup_location_id'] ?? null, 'pickup_date' => $filters['pickup_date'] ?? null, 'return_date' => $filters['return_date'] ?? null]))
<article class="car-card card card-hover flex flex-col h-full bg-white rounded-xl border border-slate-300 shadow-xs hover:shadow-md transition-all group overflow-hidden">
    {{-- Foto Unit Mobil (Rasio 16:10 Presisi, Terstandarisasi & Bebas Distorsi) --}}
    <a href="{{ route('cars.show', ['car' => $car] + $query) }}" 
       class="car-card-media relative block w-full overflow-hidden bg-slate-100 rounded-t-xl shrink-0 aspect-[16/10]"
       aria-label="Lihat detail spesifikasi armada {{ $car->name }}">
        @include('partials.car-image', [
            'car' => $car,
            'class' => 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105'
        ])
    </a>

    {{-- Badan Kartu (Flex-1 & Flex-col untuk Menjamin Kesejajaran Presisi di Seluruh Baris) --}}
    <div class="car-card-body flex flex-1 flex-col p-5">
        {{-- Kategori & Status Badge Row --}}
        <div class="car-card-badge-row flex items-center justify-between gap-2 mb-2 min-h-[24px]">
            <span class="badge badge-accent text-[11px] font-black uppercase tracking-wider">
                {{ $car->category->name }}
            </span>
            @if ($car->status !== \App\Models\Car::STATUS_AVAILABLE)
                <span class="badge badge-warning text-[10px] font-extrabold uppercase tracking-wider">
                    {{ $car->statusLabel() }}
                </span>
            @endif
        </div>

        {{-- Nama Unit Mobil (Tinggi Terstandarisasi agar 1 Baris & 2 Baris Tetap Sejajar) --}}
        <h3 class="car-card-title font-extrabold text-base uppercase text-slate-900 tracking-wide leading-snug min-h-[2.75rem] flex items-center">
            <a href="{{ route('cars.show', ['car' => $car] + $query) }}" 
               class="hover:text-[#F5A623] transition-colors line-clamp-2"
               title="{{ $car->name }}">
                {{ $car->name }}
            </a>
        </h3>

        {{-- Spesifikasi Unit (Transmisi, Kapasitas Kursi, Bahan Bakar) --}}
        <div class="car-card-specs mt-1 flex items-center flex-wrap gap-2 text-xs text-slate-500 min-h-[20px]">
            <span class="car-card-specs-item font-bold text-slate-700">{{ $car->transmissionLabel() }}</span>
            <span class="car-card-specs-dot text-slate-300 select-none">•</span>
            <span class="car-card-specs-item font-medium text-slate-600">{{ $car->seats }} Kursi</span>
            <span class="car-card-specs-dot text-slate-300 select-none">•</span>
            <span class="car-card-specs-item font-medium text-slate-600">{{ $car->fuel }}</span>
        </div>

        {{-- Bagian Bawah: Tarif Sewa & Tombol Aksi (Terkunci Presisi di Bawah via mt-auto) --}}
        <div class="car-card-footer mt-auto pt-4">
            {{-- Garis Pemisah & Tarif Sewa (Kontras Tinggi WCAG AA) --}}
            <div class="car-card-price-block pt-3 border-t border-slate-300 flex items-baseline justify-between min-h-[46px]">
                <div>
                    <span class="car-card-price-kicker text-[10px] uppercase tracking-wider text-slate-500 block font-extrabold">Tarif Sewa</span>
                    <p class="car-card-price-val text-xl font-black text-amber-700 leading-tight">
                        {{ rupiah($car->daily_price) }} <span class="car-card-price-period text-xs font-normal text-slate-500">/ hari</span>
                    </p>
                </div>
                @if (!empty($days))
                    <div class="text-right">
                        <span class="text-[10px] text-slate-500 block font-medium">Estimasi {{ $days }} hari:</span>
                        <span class="text-xs font-black text-slate-900">{{ rupiah($car->daily_price * $days) }}</span>
                    </div>
                @endif
            </div>

            {{-- Tombol Aksi (Detail & Pesan) - Selalu Sejajar Horisontal & Target Sentuh >= 44px --}}
            <div class="car-card-actions mt-3.5 grid grid-cols-2 gap-2.5">
                <a href="{{ route('cars.show', ['car' => $car] + $query) }}" 
                   class="btn btn-outline text-center text-xs py-2.5 px-3 uppercase font-bold tracking-wider border-slate-300 text-slate-700 hover:border-[#F5A623] hover:text-[#F5A623] min-h-[44px] flex items-center justify-center transition"
                   aria-label="Lihat spesifikasi lengkap {{ $car->name }}">
                    Detail
                </a>
                @if ($car->status === \App\Models\Car::STATUS_AVAILABLE)
                    <a href="{{ route('bookings.create', ['car' => $car] + $query) }}" 
                       class="btn btn-primary text-center text-xs py-2.5 px-3 uppercase font-black tracking-wider min-h-[44px] flex items-center justify-center shadow-xs hover:shadow transition"
                       aria-label="Pesan sewa unit {{ $car->name }}">
                        Pesan
                    </a>
                @else
                    <button type="button" disabled 
                            class="btn btn-outline opacity-50 cursor-not-allowed text-center text-xs py-2.5 px-3 uppercase border-slate-200 text-slate-400 min-h-[44px] flex items-center justify-center"
                            aria-label="Armada {{ $car->name }} sedang penuh atau tidak tersedia">
                        Penuh
                    </button>
                @endif
            </div>
        </div>
    </div>
</article>
