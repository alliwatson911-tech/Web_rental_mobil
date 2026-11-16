{{-- Foto utama mobil, atau ilustrasi elegan bila belum ada foto. Butuh relasi primaryPhoto sudah dimuat. --}}
@php
    $photo = $car->primaryPhoto;
    $url = $photo?->url();
    $webpUrl = $photo?->webpUrl();
@endphp
@if ($url)
    <picture class="{{ $class ?? 'w-full h-full' }}">
        @if ($webpUrl && $webpUrl !== $url)
            <source srcset="{{ $webpUrl }}" type="image/webp">
        @endif
        <img src="{{ $url }}" 
             alt="{{ $car->name }}" 
             class="{{ $class ?? 'w-full h-full object-cover' }}" 
             @if(!empty($style)) style="{{ $style }}" @endif
             loading="lazy"
             decoding="async">
    </picture>
@else
    <div class="{{ $class ?? 'w-full h-full' }} relative flex flex-col items-center justify-center bg-slate-50 border-b border-slate-200 text-slate-500 overflow-hidden group-hover:bg-slate-100 transition-colors"
         @if(!empty($style)) style="{{ $style }}" @endif>
        <div class="relative z-10 flex flex-col items-center">
            <div class="h-14 w-14 rounded-full bg-white border border-slate-200 flex items-center justify-center text-[#F5A623] shadow-sm group-hover:scale-110 transition-transform">
                <x-icon name="car" class="w-7 h-7" />
            </div>
            <span class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">{{ $car->brand ?? 'Armada Padaidi' }}</span>
        </div>
    </div>
@endif
