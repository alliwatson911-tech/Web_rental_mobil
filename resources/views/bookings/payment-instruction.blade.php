@extends('layouts.app')
@section('title', 'Instruksi Pembayaran - ' . $payment->reference_id)

@section('content')
<div class="mx-auto max-w-2xl py-4">
    <x-breadcrumb :back-url="route('bookings.show', $booking)" back-text="Kembali ke Detail Pesanan" :crumbs="['Pesanan' => route('bookings.show', $booking), 'Instruksi Pembayaran' => null]" />

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-emerald-300 bg-emerald-50 p-4 text-xs text-emerald-800 flex items-center gap-2">
            <x-icon name="check" class="w-4 h-4 text-emerald-600 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($payment->isSuccessful())
        <div class="card card-accent-top p-8 text-center space-y-4 bg-white border border-slate-200 shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">
                <x-icon name="check" class="w-8 h-8 text-emerald-600" />
            </div>
            <h1 class="text-2xl font-black uppercase text-slate-900 tracking-wide">Pembayaran Berhasil!</h1>
            <p class="text-xs text-slate-700">
                Tagihan sebesar <strong class="text-emerald-700">{{ rupiah($payment->amount) }}</strong> telah berhasil kami terima secara otomatis.
            </p>
            <div class="pt-4 flex justify-center gap-3">
                <a href="{{ route('bookings.show', $booking) }}" class="btn btn-primary text-xs py-2.5 px-5 uppercase font-black">Lihat Status Pesanan</a>
                <a href="{{ route('bookings.invoice', $booking) }}" target="_blank" class="btn btn-outline text-xs py-2.5 px-5 uppercase font-bold">Lihat Invoice</a>
            </div>
        </div>
    @elseif ($payment->status === \App\Models\Payment::STATUS_EXPIRED || $payment->isExpired())
        <div class="card p-8 text-center space-y-4 bg-white border border-slate-200 shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 text-rose-700 border border-rose-200">
                <x-icon name="cross" class="w-8 h-8 text-rose-600" />
            </div>
            <h1 class="text-2xl font-black uppercase text-slate-900 tracking-wide">Tagihan Telah Kedaluwarsa</h1>
            <p class="text-xs text-slate-600">
                Batas waktu pembayaran untuk transaksi ini telah habis. Silakan buat instruksi pembayaran baru.
            </p>
            <div class="pt-4">
                <a href="{{ route('bookings.pay', $booking) }}" class="btn btn-primary text-xs py-2.5 px-5 uppercase font-black">Buat Pembayaran Baru</a>
            </div>
        </div>
    @else
        {{-- Status Menunggu Pembayaran (Pending) --}}
        <div class="card card-accent-top overflow-hidden bg-white border border-slate-200 p-0 shadow-sm">
            <div class="bg-amber-50 border-b border-amber-200 p-6 text-center">
                <p class="text-[11px] font-extrabold uppercase tracking-widest text-amber-800">Batas Waktu Pembayaran</p>
                <div class="mt-1.5 text-2xl font-black tracking-tight text-slate-900 font-mono" id="countdownTimer">
                    @if ($payment->expires_at)
                        {{ $payment->expires_at->diffForHumans(['parts' => 2]) }}
                    @else
                        24 Jam
                    @endif
                </div>
                <p class="mt-1 text-[11px] text-slate-600">
                    Selesaikan sebelum {{ $payment->expires_at ? tanggal($payment->expires_at, 'd M Y H:i') : '-' }} WITA
                </p>
            </div>

            <div class="p-6 md:p-8 space-y-6">
                {{-- Detail Tagihan --}}
                <div class="rounded-lg bg-slate-50 p-5 text-center border border-slate-200">
                    <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Total Nominal Tagihan</p>
                    <p class="text-3xl font-black text-amber-700 mt-1">{{ rupiah($payment->amount) }}</p>
                    <div class="mt-2.5 flex items-center justify-center gap-2 text-xs text-slate-600">
                        <span class="font-bold text-slate-900">{{ $payment->paymentTypeLabel() }}</span>
                        <span>•</span>
                        <span>Ref: <strong class="font-mono text-slate-700">{{ $payment->reference_id }}</strong></span>
                    </div>
                </div>

                {{-- Instruksi berdasarkan Kanal Pembayaran --}}
                @if ($payment->channel === \App\Models\Payment::CHANNEL_QRIS)
                    <div class="text-center space-y-4">
                        <div>
                            <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">Scan Kode QRIS di Bawah Ini</h2>
                            <p class="text-xs text-slate-600 mt-1">Bisa menggunakan aplikasi m-Banking bank apa saja atau e-Wallet (BCA, Mandiri, BRI, BNI, GoPay, OVO, Dana, ShopeePay).</p>
                        </div>
                        
                        <div class="inline-block p-4 bg-white rounded-lg shadow-md border-4 border-[#F5A623]">
                            @if ($payment->qr_code_url)
                                <img src="{{ $payment->qr_code_url }}" alt="QRIS Code" class="mx-auto h-56 w-56 object-contain" />
                            @else
                                <div class="flex h-56 w-56 flex-col items-center justify-center bg-slate-100 rounded text-slate-800">
                                    <x-icon name="qrcode" class="w-16 h-16 text-[#0b1220]" />
                                    <span class="mt-2 text-xs font-black tracking-wider uppercase text-slate-900">QRIS PADAIDI RENT CAR</span>
                                    <span class="text-[10px] text-slate-500 mt-1 font-mono">{{ $payment->reference_id }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="text-xs text-slate-600 max-w-sm mx-auto leading-relaxed">
                            Nominal akan otomatis terisi dan status terverifikasi dalam hitungan detik setelah Anda mengonfirmasi PIN di aplikasi pembayaran.
                        </div>
                    </div>
                @else
                    {{-- Virtual Account --}}
                    <div class="space-y-4">
                        <div class="rounded-lg bg-slate-50 border border-slate-200 p-5 text-center">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-amber-800">
                                Nomor {{ $payment->channelLabel() }}
                            </p>
                            <div class="mt-3 flex items-center justify-center gap-3">
                                <span id="vaNumberText" class="font-mono text-2xl font-black tracking-widest text-slate-900">
                                    {{ $payment->va_number ?: 'Menunggu nomor...' }}
                                </span>
                                @if ($payment->va_number)
                                    <button type="button" onclick="copyVA(this)" class="btn btn-outline text-xs px-3.5 py-2 font-bold uppercase min-h-[44px] transition-all">
                                        Salin
                                    </button>
                                @endif
                            </div>
                            <p class="mt-2.5 text-xs text-slate-600">
                                Nama Rekening VA: <strong class="text-slate-900">PADAIDI / {{ strtoupper($booking->customer_name) }}</strong>
                            </p>
                        </div>

                        <div class="border-t border-slate-200 pt-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-3">Petunjuk Pembayaran ATM &amp; Mobile Banking:</h3>
                            <ol class="list-decimal list-inside text-xs text-slate-600 space-y-2 leading-relaxed">
                                <li>Buka aplikasi Mobile Banking / ATM bank Anda.</li>
                                <li>Pilih menu <strong>Transfer</strong> &rarr; <strong>Virtual Account</strong> (atau Pembayaran Tagihan VA).</li>
                                <li>Masukkan nomor Virtual Account yang tertera di atas.</li>
                                <li>Pastikan nama penerima dan nominal tagihan sesuai (<strong>{{ rupiah($payment->amount) }}</strong>).</li>
                                <li>Masukkan PIN transaksi Anda untuk menyelesaikan pembayaran.</li>
                            </ol>
                        </div>
                    </div>
                @endif

                {{-- Action Refresh & WhatsApp Help --}}
                <div class="pt-5 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <button type="button" onclick="window.location.reload()" class="btn btn-primary flex-1 sm:flex-none py-2.5 px-5 text-xs uppercase font-black flex items-center justify-center gap-2 min-h-[44px]">
                        <x-icon name="refresh" class="w-4 h-4 text-[#0b1220]" />
                        <span>Cek Status Pembayaran</span>
                    </button>
                    <a href="{{ route('bookings.show', $booking) }}" class="btn btn-outline text-xs flex-1 sm:flex-none text-center py-2.5 px-5 uppercase font-bold min-h-[44px] flex items-center justify-center">
                        Lihat Halaman Pesanan
                    </a>
                </div>

                @if (!empty($business['admin_whatsapp']))
                    <div class="pt-4 border-t border-slate-200 text-center">
                        <p class="text-[11px] text-slate-500 mb-2">Butuh bantuan transaksi atau konfirmasi?</p>
                        <a href="{{ \App\Support\WhatsApp::link($business['admin_whatsapp'][0] ?? '08123456789', 'Halo Admin Padaidi, saya ingin konfirmasi pembayaran untuk pesanan ' . $booking->code . ' sebesar ' . rupiah($payment->amount)) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 text-xs font-bold text-emerald-800 hover:text-emerald-900 py-2 px-4 rounded-lg bg-emerald-50 border border-emerald-200 min-h-[44px] transition-colors">
                            <x-icon name="whatsapp" class="w-4 h-4 text-emerald-600 shrink-0" />
                            <span>Chat CS Pembayaran via WhatsApp</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
function copyVA(btn) {
    const textEl = document.getElementById('vaNumberText');
    if (!textEl) return;
    const text = textEl.innerText.trim();
    const onCopied = () => {
        if (btn) {
            const orig = btn.innerText;
            btn.innerText = '✓ Tersalin!';
            btn.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
            setTimeout(() => {
                btn.innerText = orig;
                btn.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-600');
            }, 2500);
        }
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(onCopied).catch(fallback);
    } else {
        fallback();
    }
    function fallback() {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try { document.execCommand('copy'); onCopied(); } catch (e) {}
        document.body.removeChild(ta);
    }
}
</script>
@endsection