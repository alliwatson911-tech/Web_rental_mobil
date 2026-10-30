@extends('layouts.app')
@section('title', 'Kontak & Lokasi Pool - Padaidi Rent Car')

@section('content')
<x-breadcrumb :crumbs="['Kontak & Bantuan' => null]" />

<div class="mb-8">
    <span class="section-kicker">INFORMASI &amp; RESERVASI</span>
    <h1 class="section-title text-slate-900 mb-2">Kontak</h1>
    <p class="text-slate-600 text-sm max-w-2xl">
        Hubungi tim customer service dan operasional Padaidi Rent Car. Kami siap melayani kebutuhan konsultasi armada, konfirmasi booking mendesak, hingga antar jemput bandara 24 jam nonstop.
    </p>
</div>

<div class="grid gap-6 md:grid-cols-2">
    {{-- Profil Kantor & Jam Operasional --}}
    <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 text-xs shadow-sm">
        <div class="flex items-center gap-3 mb-5">
            <div class="h-10 w-10 rounded bg-slate-50 border border-slate-200 flex items-center justify-center text-[#F5A623]">
                <x-icon name="map-pin" class="w-5 h-5" />
            </div>
            <div>
                <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">{{ $business['name'] }}</h2>
                <span class="text-slate-500 text-[11px]">Kantor Pusat &amp; Pool Armada Makassar</span>
            </div>
        </div>

        <div class="space-y-4 pt-3 border-t border-slate-200">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Alamat Kantor &amp; Pool:</span>
                <p class="text-sm font-medium text-slate-800 leading-relaxed">{{ $business['address'] }}</p>
            </div>

            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Jam layanan:</span>
                <p class="text-sm font-bold text-amber-800">{{ $business['hours'] }} (Setiap Hari Termasuk Hari Libur)</p>
            </div>

            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Email Resmi:</span>
                <p class="text-sm font-medium text-slate-800">
                    <a class="text-amber-700 underline hover:text-amber-900" href="mailto:{{ $business['email'] }}">{{ $business['email'] }}</a>
                </p>
            </div>
        </div>
    </div>

    {{-- Kontak WhatsApp Admin --}}
    <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3 mb-5">
            <div class="h-10 w-10 rounded bg-slate-50 border border-slate-200 flex items-center justify-center text-[#F5A623]">
                <x-icon name="whatsapp" class="w-5 h-5" />
            </div>
            <div>
                <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">WhatsApp admin</h2>
                <p class="text-slate-500 text-xs">Klik tombol di bawah untuk langsung terhubung dengan admin.</p>
            </div>
        </div>

        <div class="mt-4 space-y-3 pt-3 border-t border-slate-200">
            @foreach ($business['admin_whatsapp'] as $i => $wa)
                <a class="btn btn-primary w-full py-3.5 px-4 flex items-center justify-center gap-2 font-black uppercase text-xs tracking-wider" target="_blank" rel="noopener" href="{{ \App\Support\WhatsApp::link($wa, 'Halo Padaidi, saya ingin bertanya tentang sewa mobil.') }}">
                    <x-icon name="whatsapp" class="w-4 h-4 text-[#0b1220]" />
                    <span>Chat Admin {{ $i + 1 }} · {{ \App\Support\WhatsApp::display($wa) }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-5 p-3.5 rounded bg-slate-50 border border-slate-200 text-[11px] text-slate-600 leading-relaxed flex items-start gap-2.5">
            <x-icon name="sparkles" class="w-4 h-4 text-[#F5A623] shrink-0 mt-0.5" />
            <span>Untuk penjemputan darurat di Bandara Sultan Hasanuddin pada jam malam/dini hari, mohon hubungi nomor WhatsApp di atas setidaknya 2 jam sebelum keberangkatan.</span>
        </div>
    </div>

    {{-- Peta Lokasi Google Maps --}}
    <div class="card overflow-hidden bg-white border border-slate-200 p-0 md:col-span-2 shadow-sm">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Peta Lokasi Pool &amp; Kantor Padaidi</span>
            <span class="badge badge-accent">Google Maps Embed</span>
        </div>
        <iframe title="Peta lokasi" class="h-80 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q={{ urlencode($business['address']) }}&output=embed"></iframe>
    </div>
</div>
@endsection
