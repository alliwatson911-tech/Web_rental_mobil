<header class="site-header sticky top-0 z-50 border-b border-[#1e293b] bg-[#0b1220]/95 backdrop-blur" style="position: sticky; top: 0; z-index: 1000;">
    <div class="site-container flex items-center justify-between py-3.5">
        {{-- Brand Logo & Name --}}
        <a href="{{ route('home') }}" class="flex items-center gap-3 text-decoration-none group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#F5A623] rounded p-1 shrink-0" aria-label="Padaidi Rent Car Beranda">
            <img src="{{ asset('images/brand/padaidi-logo.png') }}" 
                 alt="Padaidi Rent Car Emblem" 
                 class="h-10 w-auto object-contain transition-transform group-hover:scale-105 shrink-0" 
                 width="40" 
                 height="40" />
            <div class="leading-none whitespace-nowrap">
                <span class="block text-lg font-black tracking-wider text-[#f8fafc] uppercase whitespace-nowrap">
                    <span class="text-[#F5A623]">PADAIDI</span> RENT CAR
                </span>
                <span class="block text-[10px] tracking-widest text-[#94a3b8] uppercase font-bold mt-1 whitespace-nowrap">
                    Makassar · 24 Jam
                </span>
            </div>
        </a>

        {{-- Desktop Navigation Menu --}}
        <nav class="site-nav-menu hidden items-center gap-7 text-xs font-bold uppercase tracking-wider md:flex" style="display: flex; align-items: center; gap: 1.75rem;" aria-label="Menu Utama">
            <a href="{{ route('home') }}" class="site-nav-link text-[#94a3b8] transition hover:text-[#f8fafc] px-3 py-1.5 whitespace-nowrap {{ request()->routeIs('home') ? 'text-[#F5A623] font-black' : '' }}">
                Beranda
            </a>
            <a href="{{ route('cars.index') }}" class="site-nav-link text-[#94a3b8] transition hover:text-[#f8fafc] px-3 py-1.5 whitespace-nowrap {{ request()->routeIs('cars.*') ? 'text-[#F5A623] font-black' : '' }}">
                Armada
            </a>
            <a href="{{ route('terms') }}" class="site-nav-link text-[#94a3b8] transition hover:text-[#f8fafc] px-3 py-1.5 whitespace-nowrap {{ request()->routeIs('terms') ? 'text-[#F5A623] font-black' : '' }}">
                Syarat Sewa
            </a>
            <a href="{{ route('contact') }}" class="site-nav-link text-[#94a3b8] transition hover:text-[#f8fafc] px-3 py-1.5 whitespace-nowrap {{ request()->routeIs('contact') ? 'text-[#F5A623] font-black' : '' }}">
                Kontak
            </a>
            <a href="{{ route('bookings.lookup') }}" class="site-nav-link text-[#94a3b8] transition hover:text-[#f8fafc] px-3 py-1.5 whitespace-nowrap {{ request()->routeIs('bookings.lookup*') ? 'text-[#F5A623] font-black' : '' }}">
                Cek Pesanan
            </a>
        </nav>

        {{-- Desktop Action Buttons / Auth --}}
        <div class="hidden items-center gap-3 md:flex" style="display: flex; align-items: center; gap: 0.75rem;">
            @auth
                @if (auth()->user()->isMember())
                    @php($tier = auth()->user()->tier())
                    <a href="{{ route('member.dashboard') }}" class="flex items-center gap-2.5 rounded border border-[#1e293b] bg-[#111827] px-3 py-1.5 transition hover:border-[#F5A623]" title="Akun Member">
                        <span class="grid h-7 w-7 place-items-center rounded bg-[#F5A623] text-xs font-black text-[#0b1220]">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <div class="text-left text-xs leading-tight">
                            <span class="block font-bold text-[#f8fafc] max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                            <span class="block text-[10px] font-bold text-[#F5A623]">{{ $tier?->name ?? 'Member' }}</span>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('member.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-bold uppercase tracking-wider text-[#94a3b8] hover:text-[#ef4444] transition p-2" title="Keluar">
                            Keluar
                        </button>
                    </form>
                @elseif (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-padaidi btn-padaidi-secondary btn-sm whitespace-nowrap">
                        Panel Admin &rarr;
                    </a>
                @endif
            @else
                <a href="{{ route('member.login') }}" class="btn-padaidi btn-padaidi-primary btn-sm whitespace-nowrap">
                    Member
                </a>
                <a href="{{ route('cars.index') }}" class="btn-padaidi btn-padaidi-primary btn-sm whitespace-nowrap">
                    Sewa Sekarang
                </a>
            @endauth
        </div>

        {{-- Mobile Hamburger Button (Strict min 44x44px target) --}}
        <button type="button" 
                class="flex h-11 w-11 items-center justify-center rounded border border-[#1e293b] bg-[#111827] text-[#94a3b8] hover:text-[#f8fafc] hover:border-[#F5A623] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#F5A623] md:hidden" 
                data-toggle="#nav-mobile" 
                aria-label="Buka navigasi menu"
                aria-expanded="false">
            <x-icon name="menu" class="h-6 w-6" />
        </button>
    </div>

    {{-- Mobile Navigation Drawer --}}
    <nav id="nav-mobile" class="hidden border-t border-[#1e293b] bg-[#0b1220] px-5 py-4 text-sm font-bold uppercase tracking-wider md:hidden" aria-label="Menu Seluler">
        <div class="space-y-1 pb-3">
            <a href="{{ route('home') }}" class="block py-2.5 text-[#94a3b8] hover:text-[#F5A623] {{ request()->routeIs('home') ? 'text-[#F5A623]' : '' }}">
                Beranda
            </a>
            <a href="{{ route('cars.index') }}" class="block py-2.5 text-[#94a3b8] hover:text-[#F5A623] {{ request()->routeIs('cars.*') ? 'text-[#F5A623]' : '' }}">
                Armada Mobil
            </a>
            <a href="{{ route('terms') }}" class="block py-2.5 text-[#94a3b8] hover:text-[#F5A623] {{ request()->routeIs('terms') ? 'text-[#F5A623]' : '' }}">
                Syarat & Ketentuan
            </a>
            <a href="{{ route('contact') }}" class="block py-2.5 text-[#94a3b8] hover:text-[#F5A623] {{ request()->routeIs('contact') ? 'text-[#F5A623]' : '' }}">
                Kontak & Lokasi
            </a>
            <a href="{{ route('bookings.lookup') }}" class="block py-2.5 text-[#94a3b8] hover:text-[#F5A623] {{ request()->routeIs('bookings.lookup*') ? 'text-[#F5A623]' : '' }}">
                Cek Status Pesanan
            </a>
        </div>

        <div class="border-t border-[#1e293b] pt-4">
            @auth
                @if (auth()->user()->isMember())
                    <div class="mb-3 rounded border border-[#1e293b] bg-[#111827] p-3">
                        <div class="text-xs text-[#94a3b8]">Masuk sebagai:</div>
                        <div class="font-bold text-[#f8fafc]">{{ auth()->user()->name }}</div>
                        <div class="text-xs font-bold text-[#F5A623]">Tier: {{ auth()->user()->tier()?->name ?? 'Reguler' }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('member.dashboard') }}" class="btn-padaidi btn-padaidi-secondary btn-sm text-center">Akun Saya</a>
                        <form method="POST" action="{{ route('member.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-padaidi btn-padaidi-secondary btn-sm w-full text-[#ef4444] border-[#ef4444]/40">Keluar</button>
                        </form>
                    </div>
                @elseif (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-padaidi btn-padaidi-primary btn-sm w-full text-center">Panel Admin</a>
                @endif
            @else
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <a href="{{ route('member.login') }}" class="btn-padaidi btn-padaidi-primary btn-sm text-center">
                        Member
                    </a>
                    <a href="{{ route('cars.index') }}" class="btn-padaidi btn-padaidi-primary btn-sm text-center">
                        Sewa Sekarang
                    </a>
                </div>
            @endauth
        </div>
    </nav>
</header>

