@props([
    'title' => 'Butuh Sewa Mobil Cepat di Makassar?',
    'desc' => 'Hubungi admin kami sekarang via WhatsApp. Kami siap melayani pemesanan 24 jam dengan konfirmasi cepat dan unit siap jalan.',
    'waMessage' => 'Halo Padaidi Rent Car, saya ingin booking sewa mobil.',
    'btnText' => 'Chat Admin via WhatsApp',
])

@php
    $primaryWa = $business['admin_whatsapp'][0] ?? '6282197233174';
@endphp

<section class="cta-band" aria-label="Ajakan Bertindak">
    <div class="site-container max-w-4xl mx-auto">
        <h2 class="cta-band-title">
            {{ $title }}
        </h2>
        <p class="cta-band-desc">
            {{ $desc }}
        </p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ \App\Support\WhatsApp::link($primaryWa, $waMessage) }}" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="btn-padaidi btn-padaidi-dark btn-lg shadow-xl inline-flex items-center gap-3">
                <x-icon name="whatsapp" class="w-6 h-6 fill-white" />
                <span>{{ $btnText }}</span>
            </a>
            <a href="{{ route('cars.index') }}" 
               class="btn-padaidi bg-transparent border-2 border-[#0b1220] text-[#0b1220] hover:bg-[#0b1220]/10 btn-lg font-extrabold">
                Lihat Semua Armada &rarr;
            </a>
        </div>
    </div>
</section>

