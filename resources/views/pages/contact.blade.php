@extends('layouts.app')
@section('title', 'Kontak & Lokasi Pool - Padaidi Rent Car')

@section('content')
<x-breadcrumb :crumbs="['Kontak & Bantuan' => null]" />

<div class="mb-8">
    <span class="section-kicker">INFORMASI &amp; RESERVASI</span>
    <h1 class="section-title text-slate-900 mb-2">Kontak Kami</h1>
    <p class="text-slate-600 text-sm max-w-2xl">
        Hubungi tim customer service dan operasional Padaidi Rent Car. Kami siap melayani kebutuhan konsultasi armada, konfirmasi booking mendesak, hingga antar jemput bandara 24 jam nonstop.
    </p>
</div>

<div class="grid gap-6 md:grid-cols-2">
    {{-- Profil Kantor & Jam Operasional --}}
    <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 text-xs shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-3 mb-5">
                <div class="h-10 w-10 rounded-lg bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700">
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
                        <a class="text-amber-700 underline hover:text-amber-900 font-semibold" href="mailto:{{ $business['email'] }}">{{ $business['email'] }}</a>
                    </p>
                </div>

                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Instagram Resmi:</span>
                    <p class="text-sm font-medium text-slate-800">
                        <a class="inline-flex items-center gap-1.5 text-pink-700 hover:text-pink-800 font-bold underline" href="{{ $business['instagram'] ?? 'https://www.instagram.com/cv.padaidirentcarmks/' }}" target="_blank" rel="noopener noreferrer">
                            <x-icon name="instagram" class="w-4 h-4 text-pink-600 shrink-0" />
                            <span>{{ $business['instagram_handle'] ?? '@cv.padaidirentcarmks' }} ↗</span>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-200">
            <a href="{{ $business['instagram'] ?? 'https://www.instagram.com/cv.padaidirentcarmks/' }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-between w-full p-3 rounded-lg bg-gradient-to-r from-pink-50 to-amber-50 border border-pink-200 hover:border-pink-300 transition group">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-white shadow-sm">
                        <x-icon name="instagram" class="w-4 h-4 text-white" />
                    </span>
                    <div class="text-left">
                        <span class="block text-xs font-black text-slate-900 group-hover:text-pink-700">Follow Instagram Resmi</span>
                        <span class="block text-[10px] text-slate-600">{{ $business['instagram_handle'] ?? '@cv.padaidirentcarmks' }}</span>
                    </div>
                </div>
                <span class="text-xs font-bold text-pink-700 group-hover:translate-x-0.5 transition-transform">Lihat Galeri &rarr;</span>
            </a>
        </div>
    </div>

    {{-- Kontak WhatsApp Admin --}}
    <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-3 mb-5">
                <div class="h-10 w-10 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                    <x-icon name="whatsapp" class="w-5 h-5 text-emerald-600" />
                </div>
                <div>
                    <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">WhatsApp Customer Service</h2>
                    <p class="text-slate-500 text-xs">Klik nomor di bawah untuk langsung terhubung dengan admin via WhatsApp.</p>
                </div>
            </div>

            <div class="space-y-3 pt-3 border-t border-slate-200">
                @foreach ($business['admin_whatsapp'] as $i => $wa)
                    <a class="btn btn-primary w-full py-3.5 px-4 flex items-center justify-center gap-2 font-black uppercase text-xs tracking-wider shadow-sm hover:shadow transition" target="_blank" rel="noopener" href="{{ \App\Support\WhatsApp::link($wa, 'Halo Padaidi Rent Car, saya ingin bertanya tentang sewa mobil.') }}">
                        <x-icon name="whatsapp" class="w-4 h-4 text-[#0b1220]" />
                        <span>Chat Admin {{ $i + 1 }} · {{ \App\Support\WhatsApp::display($wa) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="mt-5 p-3.5 rounded-lg bg-slate-50 border border-slate-200 text-[11px] text-slate-600 leading-relaxed flex items-start gap-2.5">
                <x-icon name="sparkles" class="w-4 h-4 text-[#F5A623] shrink-0 mt-0.5" />
                <span>Untuk penjemputan darurat di Bandara Sultan Hasanuddin pada jam malam atau dini hari, mohon hubungi nomor WhatsApp di atas setidaknya 2 jam sebelum keberangkatan.</span>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
            <span class="inline-flex items-center gap-1.5 font-bold text-emerald-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Admin Online 24 Jam
            </span>
            <span>Respon Cepat &amp; Ramah</span>
        </div>
    </div>

    {{-- Banner Media Sosial Instagram Resmi --}}
    <div class="card p-6 md:p-8 bg-white border border-slate-200 shadow-sm md:col-span-2">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div class="relative shrink-0">
                    <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 p-0.5 shadow-md flex items-center justify-center">
                        <div class="h-full w-full rounded-2xl bg-slate-900 flex items-center justify-center p-2">
                            <img src="{{ asset('images/brand/padaidi-logo.png') }}" alt="Padaidi Rent Car" class="h-full w-full object-contain">
                        </div>
                    </div>
                    <span class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-pink-600 text-white shadow ring-2 ring-white">
                        <x-icon name="instagram" class="w-3.5 h-3.5 text-white" />
                    </span>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg sm:text-xl font-black uppercase text-slate-900 tracking-wide">
                            {{ $business['instagram_handle'] ?? '@cv.padaidirentcarmks' }}
                        </h2>
                        <span class="badge bg-pink-100 text-pink-800 border border-pink-300 text-[10px] font-extrabold uppercase">
                            Official Instagram
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Akun resmi <strong>Padaidi Rent Car Makassar</strong>. Ikuti untuk melihat pembaruan unit armada siap sewa, promo spesial mudik &amp; hari raya, dokumentasi serah terima pelanggan, dan tips perjalanan di Sulawesi Selatan.
                    </p>
                    <div class="mt-2.5 flex flex-wrap gap-3 text-[11px] text-slate-600 font-semibold">
                        <span class="inline-flex items-center gap-1">
                            <span class="text-pink-600 font-black">3.300+</span> Pengikut
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1">
                            <span class="text-slate-900 font-bold">Galeri Unit Ready</span>
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1">
                            <span class="text-slate-900 font-bold">Fast Respon WA</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row md:flex-col gap-2.5 shrink-0">
                <a href="{{ $business['instagram'] ?? 'https://www.instagram.com/cv.padaidirentcarmks/' }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="btn bg-gradient-to-r from-pink-600 via-rose-600 to-amber-600 hover:from-pink-700 hover:via-rose-700 hover:to-amber-700 text-white text-xs py-3 px-6 font-black uppercase tracking-wider flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition">
                    <x-icon name="instagram" class="w-4 h-4 text-white" />
                    <span>Buka Instagram Resmi ↗</span>
                </a>
                <a href="https://www.instagram.com/direct/t/cv.padaidirentcarmks/" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="btn btn-outline text-xs py-2 px-4 font-bold border-slate-300 text-slate-700 hover:border-pink-600 hover:text-pink-700 flex items-center justify-center gap-1.5 transition">
                    <span>Kirim Pesan / DM</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Peta Lokasi Google Maps --}}
    <div class="card overflow-hidden bg-white border border-slate-200 p-0 md:col-span-2 shadow-sm">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Peta Lokasi Pool &amp; Kantor Padaidi</span>
            <a href="https://maps.google.com/?q={{ urlencode($business['address']) }}" target="_blank" rel="noopener noreferrer" class="badge badge-accent hover:opacity-90 transition">
                Buka di Google Maps ↗
            </a>
        </div>
        <iframe title="Peta lokasi" class="h-80 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q={{ urlencode($business['address']) }}&output=embed"></iframe>
    </div>
</div>
@endsection
