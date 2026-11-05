@php($query = array_filter(['pickup_location_id' => $filters['pickup_location_id'] ?? null, 'pickup_date' => $filters['pickup_date'] ?? null, 'return_date' => $filters['return_date'] ?? null]))
<article class="car-card card card-hover flex flex-col h-full bg-white rounded-xl border border-slate-300 shadow-xs hover:shadow-md transition-all group overflow-hidden" 
         style="display: flex; flex-direction: column; height: 100%; min-height: 100%; padding: 0; box-sizing: border-box; background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden;">
    
    {{-- Foto Unit Mobil (Tinggi Terkunci Presisi 200px, Rasio Bebas Distorsi) --}}
    <a href="{{ route('cars.show', ['car' => $car] + $query) }}" 
       class="car-card-media relative block w-full overflow-hidden bg-slate-100 rounded-t-xl shrink-0"
       style="position: relative; width: 100%; height: 200px; min-height: 200px; max-height: 200px; overflow: hidden; display: block; border-bottom: 1px solid #e2e8f0; margin: 0; padding: 0; box-sizing: border-box; background-color: #f1f5f9;"
       aria-label="Lihat detail spesifikasi armada {{ $car->name }}">
        @include('partials.car-image', [
            'car' => $car,
            'class' => 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105',
            'style' => 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; min-height: 100%; max-height: 100%; object-fit: cover; object-position: center; display: block; margin: 0; padding: 0;'
        ])
    </a>

    {{-- Badan Kartu (Flex-1 & Flex-col untuk Menjamin Kesejajaran Presisi di Seluruh Baris) --}}
    <div class="car-card-body flex flex-1 flex-col p-5" 
         style="display: flex; flex-direction: column; flex: 1 1 auto; padding: 18px 20px 20px 20px; box-sizing: border-box;">
        
        {{-- Kategori & Status Badge Row (Tinggi Konsisten 26px di Setiap Kartu) --}}
        <div class="car-card-badge-row flex items-center justify-between gap-2 mb-2" 
             style="display: flex; align-items: center; justify-content: space-between; gap: 8px; min-height: 26px; height: 26px; margin-bottom: 8px;">
            <span class="badge badge-accent text-[11px] font-black uppercase tracking-wider">
                {{ $car->category->name }}
            </span>
            @if ($car->status !== \App\Models\Car::STATUS_AVAILABLE)
                <span class="badge badge-warning text-[10px] font-black uppercase tracking-wider">
                    {{ $car->statusLabel() }}
                </span>
            @endif
        </div>

        {{-- Nama Unit Mobil (Tinggi Terstandarisasi 2.75rem agar 1 Baris & 2 Baris Tetap Sejajar) --}}
        <h3 class="car-card-title font-extrabold text-base uppercase text-slate-900 tracking-wide leading-snug" 
            style="font-size: 1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.025em; color: #0f172a; line-height: 1.35; height: 2.75rem; min-height: 2.75rem; max-height: 2.75rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin: 0 0 6px 0;">
            <a href="{{ route('cars.show', ['car' => $car] + $query) }}" 
               class="hover:text-[#F5A623] transition-colors"
               title="{{ $car->name }}">
                {{ $car->name }}
            </a>
        </h3>

        {{-- Spesifikasi Unit (Transmisi, Kapasitas Kursi, Bahan Bakar - Tinggi Terkunci 20px) --}}
        <div class="car-card-specs mt-1 flex items-center flex-wrap gap-2 text-xs text-slate-500" 
             style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 0.75rem; color: #64748b; height: 20px; min-height: 20px; max-height: 20px; overflow: hidden; margin-bottom: 14px;">
            <span class="car-card-specs-item font-bold text-slate-700">{{ $car->transmissionLabel() }}</span>
            <span class="car-card-specs-dot text-slate-300 select-none">•</span>
            <span class="car-card-specs-item font-medium text-slate-600">{{ $car->seats }} Kursi</span>
            <span class="car-card-specs-dot text-slate-300 select-none">•</span>
            <span class="car-card-specs-item font-medium text-slate-600">{{ $car->fuel }}</span>
        </div>

        {{-- Bagian Bawah: Tarif Sewa & Tombol Aksi (Terkunci Presisi di Bawah via mt-auto) --}}
        <div class="car-card-footer mt-auto pt-3.5 border-t border-slate-300" 
             style="margin-top: auto; padding-top: 14px; border-top: 1px solid #cbd5e1; box-sizing: border-box;">
            
            {{-- Garis Pemisah & Tarif Sewa (Tinggi Konsisten 44px) --}}
            <div class="car-card-price-block flex items-baseline justify-between gap-2" 
                 style="display: flex; align-items: baseline; justify-content: space-between; gap: 8px; min-height: 44px;">
                <div>
                    <span class="car-card-price-kicker text-[10px] uppercase tracking-wider text-slate-500 block font-extrabold mb-0.5" 
                          style="font-size: 0.625rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; display: block; margin-bottom: 2px;">
                        Tarif Sewa
                    </span>
                    <p class="car-card-price-val text-xl font-black text-amber-700 leading-tight" 
                       style="font-size: 1.25rem; font-weight: 900; color: #b45309; line-height: 1.25; margin: 0;">
                        {{ rupiah($car->daily_price) }} 
                        <span class="car-card-price-period text-xs font-semibold text-slate-500" 
                              style="font-size: 0.75rem; font-weight: 600; color: #64748b;">/hari</span>
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
            <div class="car-card-actions mt-3.5 grid grid-cols-2 gap-2.5" 
                 style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 12px;">
                <a href="{{ route('cars.show', ['car' => $car] + $query) }}" 
                   class="btn btn-outline text-center text-xs py-2.5 px-3 uppercase font-bold tracking-wider border-slate-300 text-slate-700 hover:border-[#F5A623] hover:text-[#F5A623] min-h-[44px] flex items-center justify-center transition"
                   style="min-height: 44px; height: 44px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; display: flex; align-items: center; justify-content: center; padding: 8px 12px; box-sizing: border-box; border-radius: 6px;"
                   aria-label="Lihat spesifikasi lengkap {{ $car->name }}">
                    Detail
                </a>
                @if ($car->status === \App\Models\Car::STATUS_AVAILABLE)
                    <a href="{{ route('bookings.create', ['car' => $car] + $query) }}" 
                       class="btn btn-primary text-center text-xs py-2.5 px-3 uppercase font-black tracking-wider min-h-[44px] flex items-center justify-center shadow-xs hover:shadow transition"
                       style="min-height: 44px; height: 44px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; display: flex; align-items: center; justify-content: center; padding: 8px 12px; box-sizing: border-box; border-radius: 6px;"
                       aria-label="Pesan sewa unit {{ $car->name }}">
                        Pesan
                    </a>
                @else
                    <button type="button" disabled 
                            class="btn btn-outline opacity-50 cursor-not-allowed text-center text-xs py-2.5 px-3 uppercase border-slate-200 text-slate-400 min-h-[44px] flex items-center justify-center"
                            style="min-height: 44px; height: 44px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; display: flex; align-items: center; justify-content: center; padding: 8px 12px; box-sizing: border-box; border-radius: 6px;"
                            aria-label="Armada {{ $car->name }} sedang penuh atau tidak tersedia">
                        Penuh
                    </button>
                @endif
            </div>
        </div>
    </div>
</article>
