@extends('layouts.app')
@section('title', 'Sewa Mobil Makassar Lepas Kunci & Driver 24 Jam')

@section('hero')
<section class="hero-section relative overflow-hidden bg-white border-b border-slate-200 pt-12 pb-16 md:pt-16 md:pb-24" style="position: relative; overflow: hidden; isolation: isolate;">
    <div class="site-container relative z-10">
        <div class="grid gap-10 md:grid-cols-12 md:items-center">
            {{-- Left Column: Hero Copy & Stats --}}
            <div class="md:col-span-7">
                {{-- Trust Pill --}}
                <div class="inline-flex items-center gap-2 bg-amber-50 text-amber-800 px-3.5 py-1.5 rounded border border-amber-200 text-xs font-bold uppercase tracking-wider mb-4">
                    <x-icon name="shield" class="w-4 h-4 text-amber-600" />
                    <span>SEWA MOBIL RESMI MAKASSAR · BUKA 24 JAM NONSTOP</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black uppercase tracking-tight text-slate-900 leading-[1.1] mb-5">
                    Rental Mobil Makassar, <span class="text-amber-600 font-black">Lepas Kunci</span> &amp; Sopir Berpengalaman.
                </h1>

                <p class="text-slate-600 text-base md:text-lg leading-relaxed max-w-xl mb-8">
                    Solusi transportasi terbaik untuk kebutuhan dinas kantor, bisnis, maupun liburan di Sulawesi Selatan. Armada tahun muda, bersih steril, harga transparan, dan siap antar langsung ke Bandara Internasional Sultan Hasanuddin (UPG), hotel, atau alamat Anda.
                </p>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-3 mb-10">
                    <a href="#cari-mobil" class="btn btn-primary py-3 px-6 font-black uppercase text-xs tracking-wider shadow-sm hover:shadow transition">
                        Cari Mobil Tersedia
                    </a>
                    <a href="{{ route('cars.index') }}" class="btn btn-outline py-3 px-6 font-bold uppercase text-xs tracking-wider border-slate-300 text-slate-800 hover:border-[#F5A623] hover:text-[#F5A623]">
                        Lihat Semua Armada
                    </a>
                    <a href="{{ \App\Support\WhatsApp::link($business['admin_whatsapp'][0] ?? '6282197233174', 'Halo Padaidi Rent Car, saya ingin tanya ketersediaan armada mobil.') }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline py-3 px-5 font-bold uppercase text-xs tracking-wider border-emerald-500 text-emerald-700 hover:bg-emerald-50 inline-flex items-center gap-2">
                        <x-icon name="whatsapp" class="w-4 h-4 text-emerald-600" />
                        <span>Chat CS 24 Jam</span>
                    </a>
                </div>

                {{-- Stats Block --}}
                <div class="stats-grid pt-6 border-t border-slate-200" style="margin-top: 1.5rem;">
                    <div>
                        <h4 class="stats-number font-black text-slate-900" style="color: #0f172a !important;">50+ Unit</h4>
                        <p class="stats-label font-bold uppercase tracking-wider" style="color: #475569 !important;">Armada Terawat</p>
                    </div>
                    <div>
                        <h4 class="stats-number font-black text-slate-900" style="color: #0f172a !important;">24 Jam</h4>
                        <p class="stats-label font-bold uppercase tracking-wider" style="color: #475569 !important;">Antar Bandara UPG</p>
                    </div>
                    <div>
                        <h4 class="stats-number font-black text-slate-900" style="color: #0f172a !important;">100%</h4>
                        <p class="stats-label font-bold uppercase tracking-wider" style="color: #475569 !important;">Harga Transparan</p>
                    </div>
                    <div>
                        <h4 class="stats-number font-black text-slate-900 flex items-center gap-1" style="color: #0f172a !important;">
                            <span>4.9</span>
                            <x-icon name="star" class="w-5 h-5 text-[#F5A623]" />
                        </h4>
                        <p class="stats-label font-bold uppercase tracking-wider" style="color: #475569 !important;">Rating Kepuasan</p>
                    </div>
                </div>
            </div>

            {{-- Right Column: Signature Offset Frame Visual --}}
            <div class="md:col-span-5 flex justify-center items-center">
                <div class="hero-offset-frame relative w-full max-w-[320px] sm:max-w-[340px] shadow-xl">
                    <img src="{{ asset('images/brand/padaidi-hero.jpg') }}" alt="Armada Rental Mobil Padaidi Makassar" class="w-full h-full object-cover rounded block" width="340" height="255">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Search Card (Reservasi Fleksibel - Positioned with generous spacing below section boundary) --}}
<div id="cari-mobil" class="site-container mt-12 md:mt-16 relative z-20">
    <div class="card card-accent-top p-6 md:p-8 shadow-xl bg-white border border-slate-200">
        {{-- Header & Mode Tabs --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
            <div>
                <span class="section-kicker mb-1">RESERVASI FLEKSIBEL</span>
                <h2 class="text-xl md:text-2xl font-black uppercase text-slate-900 tracking-wide flex items-center gap-2">
                    <x-icon name="calendar" class="w-6 h-6 text-[#F5A623]" />
                    <span>Cari mobil yang tersedia</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1">Pilih tanggal dan lokasi pengambilan untuk melihat ketersediaan unit secara langsung.</p>
            </div>
            
            {{-- Service Highlights Badges --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Lepas Kunci Tersedia
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Dengan Sopir Siap
                </span>
                <span class="badge badge-accent">Konfirmasi Instan</span>
            </div>
        </div>

        @include('partials.search-form', ['filters' => ['pickup_date' => now()->addDay()->toDateString(), 'return_date' => now()->addDays(3)->toDateString()]])

        {{-- Trust micro-copy below form --}}
        <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
            <div class="flex flex-wrap items-center gap-4">
                <span class="flex items-center gap-1 text-slate-700 font-medium">
                    <x-icon name="check" class="w-3.5 h-3.5 text-emerald-600" /> Tanpa Biaya Tersembunyi
                </span>
                <span class="flex items-center gap-1 text-slate-700 font-medium">
                    <x-icon name="check" class="w-3.5 h-3.5 text-emerald-600" /> Buka 24 Jam Nonstop
                </span>
                <span class="flex items-center gap-1 text-slate-700 font-medium">
                    <x-icon name="check" class="w-3.5 h-3.5 text-emerald-600" /> Siaga Antar Bandara Hasanuddin
                </span>
            </div>
            <a href="{{ route('member.login') }}" class="text-amber-700 hover:text-amber-900 hover:underline font-bold inline-flex items-center gap-1">
                <x-icon name="sparkles" class="w-3.5 h-3.5 text-amber-600" />
                <span>Punya akun member? Login untuk klaim diskon hingga 10% →</span>
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
{{-- Kategori Armada --}}
<section class="mt-16">
    <x-section-header
        kicker="PILIHAN LENGKAP"
        title="Kategori Armada"
        desc="Temukan kendaraan yang paling sesuai dengan kebutuhan mobilitas harian, perjalanan dinas, maupun liburan keluarga di Sulawesi Selatan."
    />
    <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        @foreach ($categories as $category)
            <a href="{{ route('cars.index', ['category' => $category->slug]) }}" class="card card-hover p-5 border border-slate-200 bg-white flex flex-col justify-between group shadow-sm hover:shadow-md">
                <div>
                    <div class="h-12 w-12 rounded bg-slate-50 border border-slate-200 flex items-center justify-center text-[#F5A623] mb-3 group-hover:border-[#F5A623] transition-colors">
                        <x-icon name="car" class="w-6 h-6" />
                    </div>
                    <p class="font-extrabold text-base uppercase text-slate-900 tracking-wide group-hover:text-amber-600 transition-colors">{{ $category->name }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-semibold">{{ $category->cars_count }} unit siap</span>
                    <span class="text-xs text-amber-600 font-bold group-hover:translate-x-1 transition-transform">→</span>
                </div>
            </a>
        @endforeach
    </div>
</section>

{{-- Pilihan Mobil Unggulan --}}
<section class="mt-16">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
        <div>
            <span class="section-kicker">UNIT TERFAVORIT</span>
            <h2 class="section-title text-slate-900 mb-0">Pilihan Mobil Populer</h2>
        </div>
        <a href="{{ route('cars.index') }}" class="btn btn-outline text-xs py-2 px-4 uppercase font-bold tracking-wider border-slate-300 text-slate-800 hover:border-[#F5A623] hover:text-[#F5A623]">
            Lihat Semua Armada →
        </a>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($featuredCars as $car)
            @include('partials.car-card', ['car' => $car, 'filters' => []])
        @endforeach
    </div>
</section>

{{-- Membership Privilege Teaser Banner --}}
<section class="mt-20">
    <div class="member-vip-banner p-6 sm:p-8 md:p-10">
        {{-- Background Badge Watermark --}}
        <div class="absolute -right-8 -bottom-8 opacity-10 pointer-events-none">
            <x-icon name="shield" class="w-80 h-80 text-[#F5A623]" />
        </div>

        <div class="relative z-10 grid gap-8 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-7">
                <div class="inline-flex items-center gap-2 bg-[#F5A623]/20 text-[#F5A623] px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider mb-4 border border-[#F5A623]/40">
                    <x-icon name="sparkles" class="w-4 h-4 text-[#F5A623]" />
                    <span>PROGRAM LOYALITAS PADAIDI</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black uppercase text-white tracking-tight leading-tight mb-3">
                    Gabung Member, Nikmati Diskon <span class="text-[#F5A623]">Hingga 10%</span> Setiap Sewa!
                </h2>
                <p class="text-slate-300 text-sm md:text-base leading-relaxed mb-6 max-w-xl">
                    Daftar gratis dalam 1 menit. Verifikasi data SIM &amp; KTP sekali saja secara digital, dan nikmati kemudahan booking tanpa proses ulang serta potongan harga otomatis di setiap transaksi.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-2">
                    {{-- Tier Reguler --}}
                    <div class="member-tier-card member-tier-regular flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Tier Reguler</span>
                            <span class="text-xl font-black text-white">Diskon 0%</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2 leading-relaxed">Daftar instan, riwayat sewa &amp; data tersimpan aman.</p>
                    </div>

                    {{-- Tier Silver --}}
                    <div class="member-tier-card member-tier-silver flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Tier Silver</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-700 text-slate-200">2x Sewa</span>
                            </div>
                            <span class="text-xl font-black text-amber-400">Diskon 5%</span>
                        </div>
                        <p class="text-[11px] text-slate-300 mt-2 leading-relaxed">Otomatis aktif mulai sewa ke-2, prioritas alokasi unit.</p>
                    </div>

                    {{-- Tier Gold --}}
                    <div class="member-tier-card member-tier-gold flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[11px] font-black uppercase tracking-wider text-[#F5A623] flex items-center gap-1">
                                    <x-icon name="star" class="w-3.5 h-3.5 text-[#F5A623]" /> Tier Gold
                                </span>
                                <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-[#F5A623] text-[#0b1220]">VIP</span>
                            </div>
                            <span class="text-xl font-black text-[#F5A623]">Diskon 10%</span>
                        </div>
                        <p class="text-[11px] text-amber-100/90 mt-2 leading-relaxed">Mulai sewa ke-5, prioritas unit tahun termuda &amp; free cancel.</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 flex flex-col gap-3.5 justify-center">
                <a href="{{ route('member.register') }}" class="btn btn-primary py-4 px-6 font-black uppercase tracking-wider text-xs text-center shadow-xl hover:scale-[1.02] transition-transform">
                    Daftar Member Sekarang (Gratis)
                </a>
                <a href="{{ route('member.login') }}" class="btn btn-outline py-3.5 px-6 font-bold uppercase tracking-wider text-xs text-center border-slate-600 text-slate-200 hover:border-[#F5A623] hover:text-[#F5A623] bg-white/5 transition-colors">
                    Sudah Punya Akun? Masuk
                </a>
                <div class="flex items-center justify-center gap-4 text-[11px] text-slate-400 pt-2">
                    <span class="flex items-center gap-1"><x-icon name="check" class="w-3.5 h-3.5 text-emerald-400" /> 100% Gratis</span>
                    <span class="flex items-center gap-1"><x-icon name="check" class="w-3.5 h-3.5 text-emerald-400" /> Verifikasi Cepat</span>
                    <span class="flex items-center gap-1"><x-icon name="check" class="w-3.5 h-3.5 text-emerald-400" /> Diskon Otomatis</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Rute Destinasi Wisata & Bisnis Sulawesi Selatan --}}
<section class="mt-20 pt-16 border-t border-slate-200">
    <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="section-kicker">PANDUAN PERJALANAN</span>
        <h2 class="section-title text-slate-900">Rute Destinasi Terpopuler dari Makassar</h2>
        <p class="section-desc mx-auto text-slate-600">Armada kami siap menemani mobilitas Anda, mulai dari jemput bandara, rute dalam kota, hingga perjalanan lintas kabupaten di Sulawesi Selatan. Klik rute untuk langsung memilih rekomendasi armada terbaik.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        {{-- Destinasi 1: Bandara --}}
        <a href="{{ route('cars.index') }}" class="destination-card group card p-5 bg-white border border-slate-200 shadow-sm hover:border-[#F5A623] hover:shadow-lg transition-all flex flex-col justify-between cursor-pointer rounded-lg">
            <div>
                <span class="badge badge-accent mb-2">Paling Sering</span>
                <h3 class="font-extrabold text-sm uppercase text-slate-900 group-hover:text-amber-700 transition-colors mb-1">Bandara Sultan Hasanuddin</h3>
                <p class="text-xs text-slate-500 font-semibold mb-2">± 20 - 30 Menit</p>
                <p class="text-xs text-slate-600 leading-relaxed">Layanan antar-jemput 24 jam nonstop langsung di terminal kedatangan/keberangkatan.</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-700">
                <span class="flex items-center gap-1">
                    <x-icon name="car" class="w-3.5 h-3.5 text-[#F5A623]" /> Cocok Semua Tipe Mobil
                </span>
                <span class="text-xs text-amber-700 group-hover:text-amber-800 font-extrabold flex items-center gap-0.5">
                    Pilih <span class="group-hover:translate-x-1 transition-transform">→</span>
                </span>
            </div>
        </a>

        {{-- Destinasi 2: Maros & Rammang-rammang --}}
        <a href="{{ route('cars.index', ['category' => 'city-car']) }}" class="destination-card group card p-5 bg-white border border-slate-200 shadow-sm hover:border-[#F5A623] hover:shadow-lg transition-all flex flex-col justify-between cursor-pointer rounded-lg">
            <div>
                <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 mb-2">Wisata Alam</span>
                <h3 class="font-extrabold text-sm uppercase text-slate-900 group-hover:text-amber-700 transition-colors mb-1">Rammang-Rammang &amp; Maros</h3>
                <p class="text-xs text-slate-500 font-semibold mb-2">± 45 - 60 Menit</p>
                <p class="text-xs text-slate-600 leading-relaxed">Kawasan gugusan karst terbesar ke-2 di dunia dan air terjun Bantimurung yang memukau.</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-700">
                <span class="flex items-center gap-1">
                    <x-icon name="car" class="w-3.5 h-3.5 text-[#F5A623]" /> City Car / MPV Hemat
                </span>
                <span class="text-xs text-amber-700 group-hover:text-amber-800 font-extrabold flex items-center gap-0.5">
                    Pilih <span class="group-hover:translate-x-1 transition-transform">→</span>
                </span>
            </div>
        </a>

        {{-- Destinasi 3: Malino --}}
        <a href="{{ route('cars.index', ['category' => 'mpv']) }}" class="destination-card group card p-5 bg-white border border-slate-200 shadow-sm hover:border-[#F5A623] hover:shadow-lg transition-all flex flex-col justify-between cursor-pointer rounded-lg">
            <div>
                <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 mb-2">Pegunungan</span>
                <h3 class="font-extrabold text-sm uppercase text-slate-900 group-hover:text-amber-700 transition-colors mb-1">Malino Highland, Gowa</h3>
                <p class="text-xs text-slate-500 font-semibold mb-2">± 2 - 2.5 Jam</p>
                <p class="text-xs text-slate-600 leading-relaxed">Kota bunga berhawa sejuk dengan panorama hutan pinus, kebun teh, dan kafe pegunungan.</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-700">
                <span class="flex items-center gap-1">
                    <x-icon name="car" class="w-3.5 h-3.5 text-[#F5A623]" /> MPV / SUV Tangguh
                </span>
                <span class="text-xs text-amber-700 group-hover:text-amber-800 font-extrabold flex items-center gap-0.5">
                    Pilih <span class="group-hover:translate-x-1 transition-transform">→</span>
                </span>
            </div>
        </a>

        {{-- Destinasi 4: Tanjung Bira --}}
        <a href="{{ route('cars.index', ['category' => 'suv']) }}" class="destination-card group card p-5 bg-white border border-slate-200 shadow-sm hover:border-[#F5A623] hover:shadow-lg transition-all flex flex-col justify-between cursor-pointer rounded-lg">
            <div>
                <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 mb-2">Pantai Pasir Putih</span>
                <h3 class="font-extrabold text-sm uppercase text-slate-900 group-hover:text-amber-700 transition-colors mb-1">Tanjung Bira, Bulukumba</h3>
                <p class="text-xs text-slate-500 font-semibold mb-2">± 4.5 - 5 Jam</p>
                <p class="text-xs text-slate-600 leading-relaxed">Pesona laut jernih, pasir selembut tepung, serta sentra pembuatan kapal Pinisi bersejarah.</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-700">
                <span class="flex items-center gap-1">
                    <x-icon name="car" class="w-3.5 h-3.5 text-[#F5A623]" /> Innova / SUV Nyaman
                </span>
                <span class="text-xs text-amber-700 group-hover:text-amber-800 font-extrabold flex items-center gap-0.5">
                    Pilih <span class="group-hover:translate-x-1 transition-transform">→</span>
                </span>
            </div>
        </a>

        {{-- Destinasi 5: Toraja --}}
        <a href="{{ route('cars.index', ['category' => 'suv']) }}" class="destination-card group card p-5 bg-white border border-slate-200 shadow-sm hover:border-[#F5A623] hover:shadow-lg transition-all flex flex-col justify-between cursor-pointer rounded-lg">
            <div>
                <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200 mb-2">Budaya Warisan</span>
                <h3 class="font-extrabold text-sm uppercase text-slate-900 group-hover:text-amber-700 transition-colors mb-1">Tana Toraja &amp; Kete Kesu</h3>
                <p class="text-xs text-slate-500 font-semibold mb-2">± 7 - 8 Jam</p>
                <p class="text-xs text-slate-600 leading-relaxed">Eksplorasi budaya Tongkonan, kuburan batu Lemo &amp; Londa dengan driver berpengalaman.</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-700">
                <span class="flex items-center gap-1">
                    <x-icon name="car" class="w-3.5 h-3.5 text-[#F5A623]" /> Fortuner / Pajero / Hiace
                </span>
                <span class="text-xs text-amber-700 group-hover:text-amber-800 font-extrabold flex items-center gap-0.5">
                    Pilih <span class="group-hover:translate-x-1 transition-transform">→</span>
                </span>
            </div>
        </a>
    </div>
</section>

{{-- Mengapa Padaidi Rent Car (Feature Grid) --}}
<section class="mt-20 pt-16 border-t border-slate-200">
    <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="section-kicker">STANDAR LAYANAN</span>
        <h2 class="section-title text-slate-900">Mengapa Memilih Padaidi Rent Car</h2>
        <p class="section-desc mx-auto text-slate-600">Kami berdedikasi memberikan pengalaman rental mobil terbaik di Sulawesi Selatan dengan prinsip keterbukaan, kenyamanan, dan keselamatan maksimal.</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card card-accent-top p-6 bg-white border border-slate-200 shadow-sm">
            <div class="h-12 w-12 rounded bg-slate-50 border border-slate-200 flex items-center justify-center text-[#F5A623] mb-4">
                <x-icon name="key" class="w-6 h-6" />
            </div>
            <h3 class="font-extrabold text-base uppercase text-slate-900 mb-2">Lepas Kunci Cepat</h3>
            <p class="text-xs leading-relaxed text-slate-600">Bawa sendiri mobilnya dengan syarat mudah. Cukup verifikasi KTP &amp; SIM A secara digital tanpa prosedur rumit.</p>
        </div>

        <div class="card card-accent-top p-6 bg-white border border-slate-200 shadow-sm">
            <div class="h-12 w-12 rounded bg-slate-50 border border-slate-200 flex items-center justify-center text-[#F5A623] mb-4">
                <x-icon name="user" class="w-6 h-6" />
            </div>
            <h3 class="font-extrabold text-base uppercase text-slate-900 mb-2">Driver Profesional</h3>
            <p class="text-xs leading-relaxed text-slate-600">Tersedia opsi pengemudi ramah, berbusana rapi, bebas rokok, dan menguasai rute kota Makassar hingga pelosok Sulsel.</p>
        </div>

        <div class="card card-accent-top p-6 bg-white border border-slate-200 shadow-sm">
            <div class="h-12 w-12 rounded bg-slate-50 border border-slate-200 flex items-center justify-center text-[#F5A623] mb-4">
                <x-icon name="shield" class="w-6 h-6" />
            </div>
            <h3 class="font-extrabold text-base uppercase text-slate-900 mb-2">Armada Higienis &amp; Prima</h3>
            <p class="text-xs leading-relaxed text-slate-600">Seluruh unit diservis berkala di bengkel resmi, ber-AC dingin, dan dicuci bersih steril sebelum diserahterimakan.</p>
        </div>

        <div class="card card-accent-top p-6 bg-white border border-slate-200 shadow-sm">
            <div class="h-12 w-12 rounded bg-slate-50 border border-slate-200 flex items-center justify-center text-[#F5A623] mb-4">
                <x-icon name="clock" class="w-6 h-6" />
            </div>
            <h3 class="font-extrabold text-base uppercase text-slate-900 mb-2">Siaga 24 Jam Nonstop</h3>
            <p class="text-xs leading-relaxed text-slate-600">Antar-jemput fleksibel ke Bandara Sultan Hasanuddin kapan saja, termasuk jadwal penerbangan dini hari &amp; tengah malam.</p>
        </div>
    </div>
</section>

{{-- Cara Sewa Tiga Langkah --}}
<section class="mt-20 pt-16 border-t border-slate-200">
    <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="section-kicker">PROSES RINGKAS</span>
        <h2 class="section-title text-slate-900">Cara Sewa 3 Langkah Ringkas</h2>
        <p class="section-desc mx-auto text-slate-600">Pesan kendaraan impian Anda dengan alur yang cepat, transparan, dan terpercaya.</p>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        <div class="card p-6 bg-white border border-slate-200 relative shadow-sm">
            <span class="text-4xl font-black text-amber-600 font-mono block mb-2 opacity-90">01</span>
            <h3 class="font-extrabold text-base uppercase text-slate-900 mb-2">Pilih Unit &amp; Tanggal</h3>
            <p class="text-xs leading-relaxed text-slate-600">Tentukan jadwal perjalanan, lokasi pengambilan (kantor/bandara/alamat), serta kelas mobil yang sesuai kebutuhan.</p>
        </div>

        <div class="card p-6 bg-white border border-slate-200 relative shadow-sm">
            <span class="text-4xl font-black text-amber-600 font-mono block mb-2 opacity-90">02</span>
            <h3 class="font-extrabold text-base uppercase text-slate-900 mb-2">Isi Data &amp; Konfirmasi</h3>
            <p class="text-xs leading-relaxed text-slate-600">Lengkapi data penyewa, pilih layanan lepas kunci atau tambah sopir, dan lakukan konfirmasi pembayaran via QRIS, Virtual Account, atau transfer bank.</p>
        </div>

        <div class="card p-6 bg-white border border-slate-200 relative shadow-sm">
            <span class="text-4xl font-black text-amber-600 font-mono block mb-2 opacity-90">03</span>
            <h3 class="font-extrabold text-base uppercase text-slate-900 mb-2">Serah Terima &amp; Jalan</h3>
            <p class="text-xs leading-relaxed text-slate-600">Petugas pool kami menyerahkan kendaraan dalam kondisi prima dan bahan bakar sesuai perjanjian serah terima digital.</p>
        </div>
    </div>
</section>

{{-- Estimasi Biaya & Transparansi Harga --}}
<section class="mt-20 pt-16 border-t border-slate-200">
    <div class="grid gap-10 md:grid-cols-12 md:items-start">
        <div class="md:col-span-5">
            <span class="section-kicker">ESTIMASI TARIF</span>
            <h2 class="section-title text-slate-900">Harga Transparan Tanpa Biaya Tersembunyi</h2>
            <p class="section-desc text-slate-600">
                Kami percaya transparansi adalah kunci kepercayaan pelanggan. Semua tarif harian, biaya tambahan sopir, dan ketentuan overtime dijelaskan secara gamblang di awal sebelum Anda melakukan reservasi.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('terms') }}" class="btn btn-outline text-xs py-2.5 px-5 uppercase font-bold tracking-wider border-slate-300 text-slate-800 hover:border-[#F5A623] hover:text-[#F5A623]">
                    Syarat &amp; Ketentuan Sewa →
                </a>
                <a href="{{ route('cars.index') }}" class="btn btn-primary text-xs py-2.5 px-5 uppercase font-black tracking-wider">
                    Lihat Daftar Mobil
                </a>
            </div>
        </div>

        <div class="md:col-span-7">
            <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
                <div class="price-row">
                    <span class="price-row-name">City Car (Toyota Agya, Daihatsu Ayla)</span>
                    <span class="price-row-price">Mulai Rp300.000 / hari</span>
                </div>
                <div class="price-row">
                    <span class="price-row-name">MPV Keluarga (Avanza, Veloz, Xpander)</span>
                    <span class="price-row-price">Mulai Rp400.000 / hari</span>
                </div>
                <div class="price-row">
                    <span class="price-row-name">Medium MPV (Innova Reborn / Zenix)</span>
                    <span class="price-row-price">Mulai Rp600.000 / hari</span>
                </div>
                <div class="price-row">
                    <span class="price-row-name">SUV Premium (Fortuner / Pajero Sport)</span>
                    <span class="price-row-price">Mulai Rp1.100.000 / hari</span>
                </div>
                <div class="price-row">
                    <span class="price-row-name">Layanan Tambahan Driver Profesional</span>
                    <span class="price-row-price">+Rp150.000 / hari</span>
                </div>
                <div class="price-row">
                    <span class="price-row-name">Antar-Jemput Bandara Sultan Hasanuddin</span>
                    <span class="text-xs font-bold text-slate-500 uppercase">Sesuai Zona</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-4 italic">
                    *Tarif sewa mobil belum termasuk BBM, tol, dan biaya parkir. Diskon khusus otomatis bagi member terdaftar.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- FAQ (Pertanyaan yang Sering Diajukan) --}}
<section class="mt-20 pt-16 border-t border-slate-200">
    <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="section-kicker">FAQ &amp; INFORMASI</span>
        <h2 class="section-title text-slate-900">Pertanyaan yang Sering Diajukan</h2>
        <p class="section-desc mx-auto text-slate-600">Temukan jawaban cepat seputar persyaratan sewa, lokasi antar-jemput, sistem pembayaran, dan aturan sewa di Padaidi Rent Car.</p>
    </div>

    <div class="max-w-3xl mx-auto space-y-3">
        {{-- FAQ 1 --}}
        <details class="faq-item" open>
            <summary>
                <span>Apa saja persyaratan untuk sewa mobil lepas kunci?</span>
                <span class="faq-chevron">
                    <x-icon name="chevron-down" class="w-5 h-5" />
                </span>
            </summary>
            <div class="faq-body">
                Untuk layanan lepas kunci (self-drive), penyewa wajib menunjukkan dokumen asli berupa KTP Elektronik, SIM A aktif, bukti kepemilikan akun sosial media aktif, atau tiket pesawat/bukti reservasi hotel di Makassar. Bagi member terdaftar dengan e-KYC tervalidasi, proses verifikasi selesai instan tanpa perlu menyerahkan dokumen fisik berulang kali.
            </div>
        </details>

        {{-- FAQ 2 --}}
        <details class="faq-item">
            <summary>
                <span>Apakah mobil bisa diantar ke Bandara Sultan Hasanuddin atau hotel?</span>
                <span class="faq-chevron">
                    <x-icon name="chevron-down" class="w-5 h-5" />
                </span>
            </summary>
            <div class="faq-body">
                Tentu saja! Kami melayani pengantaran dan penjemputan unit 24 jam nonstop ke Bandara Internasional Sultan Hasanuddin (UPG), stasiun kereta, hotel, pelabuhan, maupun alamat domisili di seluruh wilayah Makassar, Maros, dan Gowa.
            </div>
        </details>

        {{-- FAQ 3 --}}
        <details class="faq-item">
            <summary>
                <span>Bagaimana sistem pembayaran dan uang jaminan (deposit)?</span>
                <span class="faq-chevron">
                    <x-icon name="chevron-down" class="w-5 h-5" />
                </span>
            </summary>
            <div class="faq-body">
                Pembayaran dapat dilakukan secara online melalui QRIS, Virtual Account (BCA, Mandiri, BNI, BRI), atau transfer bank. Kami menyediakan opsi bayar DP atau bayar penuh. Untuk sewa lepas kunci dikenakan uang deposit jaminan yang akan dikembalikan secara penuh dan instan maksimal 1x24 jam setelah kendaraan selesai dikembalikan dalam kondisi baik.
            </div>
        </details>

        {{-- FAQ 4 --}}
        <details class="faq-item">
            <summary>
                <span>Apakah mobil boleh dibawa keluar kota Makassar (Malino, Bira, Toraja)?</span>
                <span class="faq-chevron">
                    <x-icon name="chevron-down" class="w-5 h-5" />
                </span>
            </summary>
            <div class="faq-body">
                Boleh. Seluruh armada Padaidi terawat dengan baik dan siap untuk perjalanan dalam kota maupun luar kota seperti Malino, Tanjung Bira Bulukumba, Bone, Parepare, hingga Tana Toraja. Harap menginformasikan rencana rute tujuan Anda kepada tim kami saat pemesanan untuk penyesuaian ketentuan jarak atau layanan driver.
            </div>
        </details>

        {{-- FAQ 5 --}}
        <details class="faq-item">
            <summary>
                <span>Bagaimana cara mendapatkan diskon member hingga 10%?</span>
                <span class="faq-chevron">
                    <x-icon name="chevron-down" class="w-5 h-5" />
                </span>
            </summary>
            <div class="faq-body">
                Cukup klik menu <a href="{{ route('member.register') }}" class="text-amber-700 hover:text-amber-900 font-bold underline">Member</a> dan buat akun secara gratis. Begitu Anda menyelesaikan 2 kali sewa, akun Anda otomatis naik ke <strong>Tier Silver (Diskon 5%)</strong>, dan setelah 5 kali sewa akan naik ke <strong>Tier Gold (Diskon 10%)</strong>. Potongan harga langsung terhitung secara otomatis setiap Anda melakukan pemesanan saat login.
            </div>
        </details>
    </div>
</section>

{{-- CTA Band --}}
<section class="mt-20">
    <x-cta-band
        title="Butuh Mobil di Makassar Sekarang?"
        desc="Hubungi customer service Padaidi Rent Car via WhatsApp untuk respon cepat dan konfirmasi ketersediaan armada instan 24 jam nonstop."
        btn-text="Chat CS via WhatsApp (Respon Cepat)"
        btn-url="{{ \App\Support\WhatsApp::link($business['admin_whatsapp'][0] ?? '6282197233174', 'Halo Padaidi Rent Car, saya ingin bertanya tentang sewa mobil.') }}"
    />
</section>

{{-- Floating Quick WhatsApp Action Pill --}}
<a href="{{ \App\Support\WhatsApp::link($business['admin_whatsapp'][0] ?? '6282197233174', 'Halo Padaidi Rent Car, saya ingin konsultasi atau pesan mobil.') }}"
   target="_blank"
   rel="noopener noreferrer"
   class="floating-whatsapp-pill"
   aria-label="Hubungi WhatsApp Customer Service 24 Jam">
    <span class="live-pulse-dot" aria-hidden="true"></span>
    <x-icon name="whatsapp" class="w-4 h-4 text-[#F5A623]" />
    <span>CS 24 Jam · Tanya via WA</span>
</a>
@endsection
