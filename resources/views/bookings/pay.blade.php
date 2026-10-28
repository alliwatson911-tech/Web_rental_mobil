@extends('layouts.app')
@section('title', 'Pembayaran Online - ' . $booking->code)

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('bookings.show', $booking) }}" class="text-xs font-semibold text-brand-600 hover:underline">
                &larr; Kembali ke Detail Pesanan
            </a>
            <h1 class="mt-1 text-2xl font-black text-slate-900">Pembayaran Online Otomatis</h1>
            <p class="text-sm text-slate-500">Pilih metode pembayaran QRIS atau Virtual Account bank untuk verifikasi instan.</p>
        </div>
        <span class="rounded-lg bg-brand-50 px-3 py-1 text-sm font-bold text-brand-700">
            {{ $booking->code }}
        </span>
    </div>

    @if ($errors->any())
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid gap-6 md:grid-cols-3">
        {{-- Ringkasan Pesanan --}}
        <div class="md:col-span-1 space-y-4">
            <div class="card p-5">
                <h3 class="font-bold text-slate-900 text-sm">Ringkasan Pesanan</h3>
                <div class="mt-3 text-xs space-y-2 text-slate-600">
                    <div>
                        <span class="text-slate-400 block">Armada:</span>
                        <strong class="text-slate-800">{{ $booking->car->name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Durasi Sewa:</span>
                        <strong class="text-slate-800">{{ $booking->days }} Hari</strong>
                        <span class="block text-slate-500">{{ tanggal($booking->pickup_date, 'd M') }} - {{ tanggal($booking->return_date, 'd M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Penyewa:</span>
                        <strong class="text-slate-800">{{ $booking->customer_name }}</strong>
                    </div>
                    <hr class="border-slate-100 my-2">
                    <div class="flex justify-between">
                        <span>Total Tagihan:</span>
                        <strong class="text-slate-900">{{ rupiah($booking->grandTotal()) }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Sudah Dibayar:</span>
                        <strong class="text-emerald-700">{{ rupiah($booking->paidAmount()) }}</strong>
                    </div>
                    <div class="flex justify-between font-bold text-brand-700 text-sm pt-1 border-t border-slate-200">
                        <span>Sisa Tagihan:</span>
                        <span>{{ rupiah($remaining) }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-xs text-blue-900 leading-relaxed">
                <p class="font-bold">Keuntungan Bayar Online:</p>
                <ul class="mt-1.5 list-inside list-disc space-y-1">
                    <li>Konfirmasi otomatis 24 jam.</li>
                    <li>Tidak perlu kirim bukti transfer.</li>
                    <li>Status pesanan langsung terkonfirmasi.</li>
                </ul>
            </div>
        </div>

        {{-- Form Checkout --}}
        <div class="md:col-span-2">
            <form method="POST" action="{{ route('bookings.pay.process', $booking) }}" class="card p-6 space-y-6">
                @csrf

                {{-- Pilihan Jenis Pembayaran (DP vs Pelunasan) --}}
                <div>
                    <label class="block text-sm font-bold text-slate-900 mb-2">Pilih Jenis Pembayaran</label>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @if ($booking->paidAmount() == 0 && config('payment.dp_enabled', true))
                            <label class="cursor-pointer rounded-xl border-2 p-3 text-sm transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/40 border-slate-200 hover:border-slate-300">
                                <div class="flex items-start gap-2">
                                    <input type="radio" name="payment_type" value="dp" class="mt-1 text-brand-600 focus:ring-brand-500" {{ old('payment_type', 'dp') === 'dp' ? 'checked' : '' }} onchange="updateTotalDisplay()">
                                    <div>
                                        <p class="font-bold text-slate-900">Uang Muka (DP 30%)</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Booking tanggal sekarang, sisa dibayar nanti</p>
                                        <p class="mt-2 text-base font-extrabold text-brand-700">{{ rupiah($dpAmount) }}</p>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer rounded-xl border-2 p-3 text-sm transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/40 border-slate-200 hover:border-slate-300">
                                <div class="flex items-start gap-2">
                                    <input type="radio" name="payment_type" value="full" class="mt-1 text-brand-600 focus:ring-brand-500" {{ old('payment_type') === 'full' ? 'checked' : '' }} onchange="updateTotalDisplay()">
                                    <div>
                                        <p class="font-bold text-slate-900">Bayar Penuh (100%)</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Langsung lunas tanpa sisa tagihan</p>
                                        <p class="mt-2 text-base font-extrabold text-brand-700">{{ rupiah($booking->grandTotal()) }}</p>
                                    </div>
                                </div>
                            </label>
                        @else
                            <label class="cursor-pointer rounded-xl border-2 p-3 text-sm transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/40 border-slate-200 hover:border-slate-300 sm:col-span-2">
                                <div class="flex items-start gap-2">
                                    <input type="radio" name="payment_type" value="settlement" class="mt-1 text-brand-600 focus:ring-brand-500" checked onchange="updateTotalDisplay()">
                                    <div>
                                        <p class="font-bold text-slate-900">Pelunasan Sisa Tagihan</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Membayar sisa tagihan yang belum terbayar</p>
                                        <p class="mt-2 text-base font-extrabold text-brand-700">{{ rupiah($remaining) }}</p>
                                    </div>
                                </div>
                            </label>
                        @endif
                    </div>
                </div>

                {{-- Pilihan Kanal Pembayaran --}}
                <div>
                    <label class="block text-sm font-bold text-slate-900 mb-2">Metode Pembayaran</label>
                    <div class="space-y-3">
                        {{-- QRIS --}}
                        <label class="flex items-center justify-between cursor-pointer rounded-xl border-2 p-4 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/40 border-slate-200 hover:border-slate-300">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="channel" value="qris" class="text-brand-600 focus:ring-brand-500" {{ old('channel', 'qris') === 'qris' ? 'checked' : '' }}>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">QRIS (Semua Pembayaran)</p>
                                    <p class="text-xs text-slate-500">Scan via BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, ShopeePay</p>
                                </div>
                            </div>
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">Instan</span>
                        </label>

                        {{-- Virtual Account Bank --}}
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 pt-2">Virtual Account Bank (Otomatis)</p>

                        <div class="grid gap-2 sm:grid-cols-2">
                            <label class="flex items-center gap-3 cursor-pointer rounded-xl border p-3 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/40 border-slate-200 hover:border-slate-300 text-sm">
                                <input type="radio" name="channel" value="va_bca" class="text-brand-600 focus:ring-brand-500" {{ old('channel') === 'va_bca' ? 'checked' : '' }}>
                                <span class="font-semibold text-slate-800">BCA Virtual Account</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer rounded-xl border p-3 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/40 border-slate-200 hover:border-slate-300 text-sm">
                                <input type="radio" name="channel" value="va_mandiri" class="text-brand-600 focus:ring-brand-500" {{ old('channel') === 'va_mandiri' ? 'checked' : '' }}>
                                <span class="font-semibold text-slate-800">Mandiri Bill / VA</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer rounded-xl border p-3 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/40 border-slate-200 hover:border-slate-300 text-sm">
                                <input type="radio" name="channel" value="va_bni" class="text-brand-600 focus:ring-brand-500" {{ old('channel') === 'va_bni' ? 'checked' : '' }}>
                                <span class="font-semibold text-slate-800">BNI Virtual Account</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer rounded-xl border p-3 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/40 border-slate-200 hover:border-slate-300 text-sm">
                                <input type="radio" name="channel" value="va_bri" class="text-brand-600 focus:ring-brand-500" {{ old('channel') === 'va_bri' ? 'checked' : '' }}>
                                <span class="font-semibold text-slate-800">BRI BRIVA</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer rounded-xl border p-3 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/40 border-slate-200 hover:border-slate-300 text-sm sm:col-span-2">
                                <input type="radio" name="channel" value="va_permata" class="text-brand-600 focus:ring-brand-500" {{ old('channel') === 'va_permata' ? 'checked' : '' }}>
                                <span class="font-semibold text-slate-800">Permata Bank Virtual Account</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">Nominal yang akan dibayar:</p>
                        <p id="displayAmount" class="text-2xl font-black text-brand-700">
                            {{ rupiah($booking->paidAmount() == 0 && config('payment.dp_enabled', true) ? $dpAmount : $remaining) }}
                        </p>
                    </div>
                    <button type="submit" class="btn btn-primary px-6 py-2.5 text-base">
                        Lanjut ke Pembayaran &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateTotalDisplay() {
    const dpSelected = document.querySelector('input[name="payment_type"][value="dp"]')?.checked;
    const dpVal = '{{ rupiah($dpAmount) }}';
    const fullVal = '{{ rupiah($booking->grandTotal()) }}';
    const remVal = '{{ rupiah($remaining) }}';
    
    const display = document.getElementById('displayAmount');
    if (dpSelected) {
        display.textContent = dpVal;
    } else {
        const fullSelected = document.querySelector('input[name="payment_type"][value="full"]')?.checked;
        display.textContent = fullSelected ? fullVal : remVal;
    }
}
</script>
@endsection

