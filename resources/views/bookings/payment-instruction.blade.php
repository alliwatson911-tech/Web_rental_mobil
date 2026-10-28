@extends('layouts.app')
@section('title', 'Instruksi Pembayaran - ' . $payment->reference_id)

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-4">
        <a href="{{ route('bookings.show', $booking) }}" class="text-xs font-semibold text-brand-600 hover:underline">
            &larr; Kembali ke Detail Pesanan ({{ $booking->code }})
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($payment->isSuccessful())
        <div class="card p-8 text-center space-y-4">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-3xl font-black">
                ✓
            </div>
            <h1 class="text-2xl font-black text-slate-900">Pembayaran Berhasil!</h1>
            <p class="text-sm text-slate-600">
                Tagihan sebesar <strong>{{ rupiah($payment->amount) }}</strong> telah berhasil kami terima secara otomatis.
            </p>
            <div class="pt-2 flex justify-center gap-3">
                <a href="{{ route('bookings.show', $booking) }}" class="btn btn-primary">Lihat Status Pesanan</a>
                <a href="{{ route('bookings.invoice', $booking) }}" target="_blank" class="btn btn-outline">Lihat Invoice</a>
            </div>
        </div>
    @elseif ($payment->status === \App\Models\Payment::STATUS_EXPIRED || $payment->isExpired())
        <div class="card p-8 text-center space-y-4">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 text-rose-600 text-3xl font-black">
                ✕
            </div>
            <h1 class="text-2xl font-black text-slate-900">Tagihan Telah Kedaluwarsa</h1>
            <p class="text-sm text-slate-600">
                Batas waktu pembayaran untuk transaksi ini telah habis. Silakan buat instruksi pembayaran baru.
            </p>
            <div class="pt-2">
                <a href="{{ route('bookings.pay', $booking) }}" class="btn btn-primary">Buat Pembayaran Baru</a>
            </div>
        </div>
    @else
        {{-- Status Menunggu Pembayaran (Pending) --}}
        <div class="card overflow-hidden">
            <div class="bg-gradient-to-r from-brand-600 to-brand-800 p-6 text-white text-center">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-100">Batas Waktu Pembayaran</p>
                <div class="mt-1 text-2xl font-black tracking-tight" id="countdownTimer">
                    @if ($payment->expires_at)
                        {{ $payment->expires_at->diffForHumans(['parts' => 2]) }}
                    @else
                        24 Jam
                    @endif
                </div>
                <p class="mt-1 text-xs text-brand-100">
                    Selesaikan sebelum {{ $payment->expires_at ? tanggal($payment->expires_at, 'd M Y H:i') : '-' }} WITA
                </p>
            </div>

            <div class="p-6 space-y-6">
                {{-- Detail Tagihan --}}
                <div class="rounded-xl bg-slate-50 p-4 text-center border border-slate-200">
                    <p class="text-xs text-slate-500 font-medium">Total Nominal Tagihan</p>
                    <p class="text-3xl font-black text-brand-700 mt-0.5">{{ rupiah($payment->amount) }}</p>
                    <div class="mt-2 flex items-center justify-center gap-2 text-xs text-slate-500">
                        <span>{{ $payment->paymentTypeLabel() }}</span>
                        <span>•</span>
                        <span>Ref: <strong class="font-mono text-slate-700">{{ $payment->reference_id }}</strong></span>
                    </div>
                </div>

                {{-- Instruksi berdasarkan Kanal Pembayaran --}}
                @if ($payment->channel === \App\Models\Payment::CHANNEL_QRIS)
                    <div class="text-center space-y-3">
                        <h2 class="font-bold text-slate-900">Scan Kode QRIS di Bawah Ini</h2>
                        <p class="text-xs text-slate-500">Bisa menggunakan aplikasi m-Banking apa saja atau e-Wallet (GoPay, OVO, Dana, ShopeePay).</p>
                        
                        <div class="inline-block p-4 bg-white border-2 border-dashed border-slate-300 rounded-2xl shadow-sm">
                            @if ($payment->qr_code_url)
                                <img src="{{ $payment->qr_code_url }}" alt="QRIS Code" class="mx-auto h-56 w-56 object-contain" />
                            @else
                                <div class="flex h-56 w-56 flex-col items-center justify-center bg-slate-100 rounded-xl text-slate-500">
                                    <span class="text-4xl">📱</span>
                                    <span class="mt-2 text-xs font-bold text-slate-700">QRIS PADAIDI RENTAL</span>
                                    <span class="text-[10px] text-slate-400 mt-1 font-mono">{{ $payment->reference_id }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="text-xs text-slate-500 max-w-sm mx-auto">
                            Nominal akan otomatis terisi atau terverifikasi dalam hitungan detik setelah Anda mengonfirmasi PIN di aplikasi pembayaran.
                        </div>
                    </div>
                @else
                    {{-- Virtual Account --}}
                    <div class="space-y-4">
                        <div class="rounded-xl border border-brand-200 bg-brand-50/40 p-4 text-center">
                            <p class="text-xs font-bold uppercase tracking-wider text-brand-800">
                                Nomor {{ $payment->channelLabel() }}
                            </p>
                            <div class="mt-2 flex items-center justify-center gap-2">
                                <span id="vaNumberText" class="font-mono text-2xl font-black tracking-widest text-slate-900">
                                    {{ $payment->va_number ?: 'Menunggu nomor...' }}
                                </span>
                                @if ($payment->va_number)
                                    <button type="button" onclick="copyVA()" class="btn btn-outline text-xs px-2.5 py-1">
                                        Salin
                                    </button>
                                @endif
                            </div>
                            <p class="mt-2 text-xs text-slate-500">
                                Nama Akun: <strong>PADAIDI / {{ strtoupper($booking->customer_name) }}</strong>
                            </p>
                        </div>

                        <div class="border-t border-slate-100 pt-3">
                            <h3 class="text-sm font-bold text-slate-900 mb-2">Petunjuk Pembayaran ATM & Mobile Banking:</h3>
                            <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1.5 leading-relaxed">
                                <li>Buka aplikasi Mobile Banking / ATM bank Anda.</li>
                                <li>Pilih menu <strong>Transfer</strong> &rarr; <strong>Virtual Account</strong> (atau Pembayaran VA).</li>
                                <li>Masukkan nomor Virtual Account di atas.</li>
                                <li>Pastikan nama penerima dan nominal tagihan sesuai (<strong>{{ rupiah($payment->amount) }}</strong>).</li>
                                <li>Masukkan PIN Anda untuk menyelesaikan transaksi.</li>
                            </ol>
                        </div>
                    </div>
                @endif

                {{-- Action Refresh --}}
                <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <button type="button" onclick="window.location.reload()" class="btn btn-primary flex-1 sm:flex-none">
                        🔄 Cek Status Pembayaran
                    </button>
                    <a href="{{ route('bookings.show', $booking) }}" class="btn btn-outline text-xs flex-1 sm:flex-none text-center">
                        Lihat Halaman Pesanan
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function copyVA() {
    const text = document.getElementById('vaNumberText').innerText.trim();
    navigator.clipboard.writeText(text).then(() => {
        alert('Nomor Virtual Account disalin ke clipboard: ' + text);
    });
}
</script>
@endsection

