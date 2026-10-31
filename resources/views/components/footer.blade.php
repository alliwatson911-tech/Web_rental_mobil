<footer class="border-t border-[#1e293b] bg-[#0b1220] pt-16 pb-8 text-[#94a3b8] text-sm" style="padding-bottom: max(2rem, calc(2rem + env(safe-area-inset-bottom, 0px)));" role="contentinfo">
    <div class="site-container">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-4 pb-12 border-b border-[#1e293b]" style="display: grid; gap: 2.5rem;">
            {{-- Kolom 1: Profil Brand --}}
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 mb-4 group shrink-0" aria-label="Padaidi Rent Car">
                    <img src="{{ asset('images/brand/padaidi-logo.png') }}" 
                         alt="Padaidi Rent Car Emblem" 
                         class="h-11 w-auto object-contain shrink-0" 
                         width="44" 
                         height="44" />
                    <span class="text-xl font-black tracking-wider text-[#f8fafc] uppercase whitespace-nowrap">
                        <span class="text-[#F5A623]">PADAIDI</span> RENT CAR
                    </span>
                </a>
                <p class="text-sm text-[#94a3b8] leading-relaxed mb-4">
                    Solusi terpercaya sewa mobil lepas kunci dan dengan sopir di Makassar. Unit terawat, harga transparan tanpa biaya tersembunyi.
                </p>
                <div class="flex items-center gap-2 text-xs font-bold text-[#F5A623] uppercase tracking-wider">
                    <x-icon name="shield" class="w-4 h-4 text-[#F5A623]" />
                    <span>Unit Berasuransi & Bebas Khawatir</span>
                </div>
            </div>

            {{-- Kolom 2: Navigasi Cepat --}}
            <div>
                <h4 class="text-sm font-black uppercase tracking-wider text-[#f8fafc] mb-4">
                    Navigasi
                </h4>
                <ul class="space-y-2.5 text-xs font-semibold uppercase tracking-wider">
                    <li>
                        <a href="{{ route('home') }}" class="text-[#94a3b8] hover:text-[#F5A623] transition flex items-center gap-1.5">
                            <span class="text-[#F5A623]">›</span> Beranda Utama
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('cars.index') }}" class="text-[#94a3b8] hover:text-[#F5A623] transition flex items-center gap-1.5">
                            <span class="text-[#F5A623]">›</span> Daftar Armada Mobil
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('terms') }}" class="text-[#94a3b8] hover:text-[#F5A623] transition flex items-center gap-1.5">
                            <span class="text-[#F5A623]">›</span> Syarat & Ketentuan Sewa
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('bookings.lookup') }}" class="text-[#94a3b8] hover:text-[#F5A623] transition flex items-center gap-1.5">
                            <span class="text-[#F5A623]">›</span> Cek Status Pesanan
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}" class="text-[#94a3b8] hover:text-[#F5A623] transition flex items-center gap-1.5">
                            <span class="text-[#F5A623]">›</span> Kontak & Bantuan
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Kolom 3: Jam Operasional --}}
            <div>
                <h4 class="text-sm font-black uppercase tracking-wider text-[#f8fafc] mb-4">
                    Jam Operasional
                </h4>
                <p class="text-xs uppercase tracking-wider text-[#94a3b8] mb-1 font-bold">
                    Senin — Minggu (Setiap Hari)
                </p>
                <p class="text-lg font-black text-[#F5A623] mb-3">
                    Buka 24 Jam Nonstop
                </p>
                <p class="text-xs text-[#94a3b8] leading-relaxed">
                    Layanan antar-jemput unit ke Bandara Internasional Sultan Hasanuddin, hotel, dan alamat rumah di wilayah Makassar dan sekitarnya.
                </p>
            </div>

            {{-- Kolom 4: Hubungi Kami --}}
            <div>
                <h4 class="text-sm font-black uppercase tracking-wider text-[#f8fafc] mb-4">
                    Hubungi Kami
                </h4>
                <div class="space-y-3 text-xs text-[#94a3b8]">
                    <div class="flex items-start gap-2.5">
                        <x-icon name="map-pin" class="w-4 h-4 text-[#F5A623] shrink-0 mt-0.5" />
                        <span>{{ $business['address'] ?? 'Jl. Perintis Kemerdekaan No.1, Daya, Makassar, Sulawesi Selatan' }}</span>
                    </div>
                    @if (!empty($business['admin_whatsapp']))
                        @foreach ($business['admin_whatsapp'] as $wa)
                            <div class="flex items-center gap-2.5">
                                <x-icon name="whatsapp" class="w-4 h-4 text-[#F5A623] shrink-0" />
                                <a href="{{ \App\Support\WhatsApp::link($wa, 'Halo Padaidi Rent Car, saya ingin menanyakan ketersediaan rental mobil.') }}" target="_blank" rel="noopener" class="hover:text-[#F5A623] transition font-bold text-[#f8fafc]">
                                    {{ \App\Support\WhatsApp::display($wa) }}
                                </a>
                            </div>
                        @endforeach
                    @endif
                    <div class="flex items-center gap-2.5">
                        <x-icon name="mail" class="w-4 h-4 text-[#F5A623] shrink-0" />
                        <a href="mailto:{{ $business['email'] ?? 'info@padaidirentcar.com' }}" class="hover:text-[#F5A623] transition">
                            {{ $business['email'] ?? 'info@padaidirentcar.com' }}
                        </a>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <x-icon name="instagram" class="w-4 h-4 text-[#F5A623] shrink-0" />
                        <a href="{{ $business['instagram'] ?? 'https://www.instagram.com/cv.padaidirentcarmks/' }}" target="_blank" rel="noopener noreferrer" class="hover:text-[#F5A623] transition font-bold text-[#f8fafc]">
                            {{ $business['instagram_handle'] ?? '@cv.padaidirentcarmks' }} ↗
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Copyright --}}
        <div class="pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-[#64748b]">
            <p class="m-0">
                &copy; {{ date('Y') }} <strong>Padaidi Rent Car</strong>. Hak Cipta Dilindungi Undang-Undang.
            </p>
            <div class="flex items-center gap-5">
                <a href="{{ route('terms') }}" class="hover:text-[#94a3b8] transition">Kebijakan Privasi</a>
                <span>·</span>
                <a href="{{ route('terms') }}" class="hover:text-[#94a3b8] transition">Syarat Layanan</a>
                <span>·</span>
                <span class="text-[#F5A623] font-bold">Makassar, Sulawesi Selatan</span>
            </div>
        </div>
    </div>
</footer>

