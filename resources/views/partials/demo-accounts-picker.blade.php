@php
    $theme = $theme ?? 'light';
    $isDark = $theme === 'dark';
    
    $accounts = [
        [
            'role_title' => 'Admin / Pengurus',
            'email' => 'admin@padaidi.test',
            'password' => 'password123',
            'badge_text' => 'ADMIN',
            'badge_class' => 'bg-[#d99b26] text-white',
            'dot_color' => '#ef4444',
            'desc' => 'Akses penuh kelola rental & sistem',
            'for' => ['all', 'admin'],
        ],
        [
            'role_title' => 'Manajer Operasional',
            'email' => 'admin2@padaidi.test',
            'password' => 'password123',
            'badge_text' => 'MANAGER',
            'badge_class' => 'bg-[#0f2b48] text-white',
            'dot_color' => '#f59e0b',
            'desc' => 'Kelola armada, jadwal servis & serah terima',
            'for' => ['all', 'admin'],
        ],
        [
            'role_title' => 'Petugas CS & Reservasi',
            'email' => 'cs@padaidi.test',
            'password' => 'password123',
            'badge_text' => 'STAFF',
            'badge_class' => 'bg-sky-100 text-sky-800 border border-sky-300',
            'dot_color' => '#6366f1',
            'desc' => 'Layanan pelanggan & verifikasi pesanan',
            'for' => ['all', 'admin'],
        ],
        [
            'role_title' => 'H. Syamsuddin (Member Prioritas)',
            'email' => 'gold@padaidi.test',
            'password' => 'password123',
            'badge_text' => 'GOLD',
            'badge_class' => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
            'dot_color' => '#10b981',
            'desc' => 'Penyewa terverifikasi (diskon 10%)',
            'for' => ['all', 'member'],
        ],
        [
            'role_title' => 'Budi Santoso (Member Reguler)',
            'email' => 'member@padaidi.test',
            'password' => 'password123',
            'badge_text' => 'MEMBER',
            'badge_class' => 'bg-cyan-100 text-cyan-800 border border-cyan-300',
            'dot_color' => '#0ea5e9',
            'desc' => 'Pelanggan sewa mobil terdaftar',
            'for' => ['all', 'member'],
        ],
    ];

    $filterFor = $type ?? 'all';
    if ($filterFor !== 'all') {
        $accounts = array_filter($accounts, fn($a) => in_array($filterFor, $a['for']));
    }
@endphp

<div class="demo-account-picker-box my-5 rounded-2xl border-2 border-dashed {{ $isDark ? 'border-[#334155] bg-[#0b1220]/90' : 'border-slate-300 bg-[#fbfbfa]' }} p-4 sm:p-5 transition-all shadow-xs"
     style="border-radius: 18px; box-sizing: border-box;">
    
    {{-- Header Bar --}}
    <div class="flex items-center justify-between gap-2 pb-3 border-b {{ $isDark ? 'border-[#1e293b]' : 'border-slate-200' }}">
        <div class="flex items-center gap-2">
            <span class="text-base select-none" aria-hidden="true">🔑</span>
            <span class="text-xs font-black uppercase tracking-wider {{ $isDark ? 'text-slate-200' : 'text-slate-800' }}">
                Pilih Akun Demo untuk Uji Coba:
            </span>
        </div>
        <div class="flex items-center gap-1.5 shrink-0" title="Kata sandi untuk seluruh akun pengujian">
            <code class="px-2.5 py-1 text-xs font-mono font-bold rounded-md {{ $isDark ? 'bg-[#111827] text-[#F5A623] border border-[#1e293b]' : 'bg-white text-slate-700 border border-slate-300 shadow-2xs' }}">
                password123
            </code>
        </div>
    </div>

    {{-- Daftar Akun Stakeholder --}}
    <div class="mt-3 space-y-2.5" role="group" aria-label="Daftar Akun Pengujian">
        @foreach ($accounts as $acc)
            <button type="button"
                    class="demo-account-card group w-full text-left flex items-center justify-between gap-3 p-2.5 sm:px-3.5 sm:py-2.5 rounded-xl border transition-all cursor-pointer {{ $isDark ? 'bg-[#111827] border-[#1e293b] hover:border-[#F5A623] hover:bg-[#1a2333]' : 'bg-white border-slate-200 hover:border-amber-400 hover:shadow-md hover:bg-amber-50/20' }}"
                    data-email="{{ $acc['email'] }}"
                    data-password="{{ $acc['password'] }}"
                    data-role="{{ $acc['role_title'] }}"
                    onclick="fillDemoCredentials('{{ $acc['email'] }}', '{{ $acc['password'] }}', '{{ $acc['role_title'] }}', this)"
                    style="border-radius: 12px; transition: all 0.15s ease;">
                
                {{-- Left: Colored Dot & Account Info --}}
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-xs" 
                          style="background-color: {{ $acc['dot_color'] }};" 
                          aria-hidden="true"></span>
                    <div class="truncate">
                        <div class="text-xs sm:text-sm font-extrabold leading-tight {{ $isDark ? 'text-slate-100 group-hover:text-amber-400' : 'text-slate-900 group-hover:text-amber-700' }} transition-colors">
                            {{ $acc['role_title'] }}
                        </div>
                        <div class="text-[11px] font-mono {{ $isDark ? 'text-slate-400' : 'text-slate-500' }} truncate mt-0.5">
                            {{ $acc['email'] }}
                        </div>
                    </div>
                </div>

                {{-- Right: Role Pill Badge --}}
                <span class="demo-badge px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[10px] font-black uppercase tracking-wider shrink-0 shadow-2xs {{ $acc['badge_class'] }}"
                      style="border-radius: 9999px;">
                    {{ $acc['badge_text'] }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- Feedback Message Container --}}
    <div id="demo-feedback-banner" 
         class="hidden mt-3 p-2 rounded-lg text-xs font-bold text-center transition-all duration-300 {{ $isDark ? 'bg-amber-950/60 border border-amber-600/50 text-amber-300' : 'bg-emerald-50 border border-emerald-300 text-emerald-800' }}">
        <span id="demo-feedback-text">Akun tertempel ke form!</span>
    </div>
</div>

<script>
    function fillDemoCredentials(email, password, roleName, cardElement) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        if (!emailInput || !passwordInput) return;

        // 1. Tempel data ke input field
        emailInput.value = email;
        passwordInput.value = password;

        // 2. Tembakkan event input & change untuk reaktivitas form
        emailInput.dispatchEvent(new Event('input', { bubbles: true }));
        emailInput.dispatchEvent(new Event('change', { bubbles: true }));
        passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
        passwordInput.dispatchEvent(new Event('change', { bubbles: true }));

        // 3. Efek visual seleksi pada kartu
        document.querySelectorAll('.demo-account-card').forEach(c => {
            c.classList.remove('ring-2', 'ring-amber-500', 'border-amber-500');
        });
        if (cardElement) {
            cardElement.classList.add('ring-2', 'ring-amber-500', 'border-amber-500');
        }

        // 4. Highlight pada input form
        emailInput.classList.add('ring-2', 'ring-amber-400');
        passwordInput.classList.add('ring-2', 'ring-amber-400');
        setTimeout(() => {
            emailInput.classList.remove('ring-2', 'ring-amber-400');
            passwordInput.classList.remove('ring-2', 'ring-amber-400');
        }, 1200);

        // 5. Tampilkan status banner
        const feedback = document.getElementById('demo-feedback-banner');
        const feedbackText = document.getElementById('demo-feedback-text');
        if (feedback && feedbackText) {
            feedbackText.innerHTML = '✓ Akun <strong>' + roleName + '</strong> siap digunakan! Silakan klik Masuk.';
            feedback.classList.remove('hidden');
        }

        // 6. Arahkan fokus ke tombol submit
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.focus();
        }
    }
</script>

