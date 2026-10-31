@extends('layouts.app')
@section('title', 'Pembayaran Online - ' . $booking->code)

@section('content')
<div class="mx-auto max-w-3xl">
    <x-breadcrumb :back-url="route('bookings.show', $booking)" back-text="Kembali ke Detail Pesanan" :crumbs="['Pesanan' => route('bookings.show', $booking), 'Pembayaran Online' => null]" />

    <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-6">
        <div>
            <a href="{{ route('bookings.show', $booking) }}" class="text-xs font-bold uppercase tracking-wider text-amber-700 hover:text-amber-800">
                &larr; Kembali ke Detail Pesanan
            </a>
            <h1 class="mt-2 text-2xl font-black uppercase text-slate-900 tracking-wide">Pembayaran Online Otomatis</h1>
            <p class="text-xs text-slate-600 mt-0.5">Pilih metode pembayaran QRIS atau Virtual Account bank untuk verifikasi instan 24 jam.</p>
        </div>
        <span class="badge badge-accent font-mono text-sm py-1.5 px-3">
            {{ $booking->code }}
        </span>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded border border-rose-300 bg-rose-50 p-4 text-xs text-rose-800">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-3 items-start">
        {{-- Ringkasan Pesanan --}}
        <div class="md:col-span-1 space-y-4">
            <div class="card card-accent-top p-5 bg-white border border-slate-200 shadow-sm">
                <h3 class="font-extrabold text-sm uppercase text-slate-900 tracking-wide">Ringkasan Pesanan</h3>
                <div class="mt-3 text-xs space-y-2.5 text-slate-700">
                    <div>
                        <span class="text-slate-500 text-[10px] uppercase font-bold block">Armada:</span>
                        <strong class="text-slate-900 text-sm">{{ $booking->car->name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 text-[10px] uppercase font-bold block">Durasi Sewa:</span>
                        <strong class="text-slate-900">{{ $booking->days }} Hari</strong>
                        <span class="block text-slate-600 text-[11px] mt-0.5">{{ tanggal($booking->pickup_date, 'd M') }} – {{ tanggal($booking->return_date, 'd M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-[10px] uppercase font-bold block">Penyewa:</span>
                        <strong class="text-slate-900">{{ $booking->customer_name }}</strong>
                    </div>
                    <div class="border-t border-slate-200 pt-2 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Total Tagihan:</span>
                            <strong class="text-slate-900">{{ rupiah($booking->grandTotal()) }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Sudah Dibayar:</span>
                            <strong class="text-emerald-700 font-bold">{{ rupiah($booking->paidAmount()) }}</strong>
                        </div>
                        <div class="flex justify-between font-bold text-slate-900 text-sm pt-2 border-t border-slate-200">
                            <span>Sisa Tagihan:</span>
                            <span class="text-amber-700 font-black">{{ rupiah($remaining) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded border border-amber-200 bg-amber-50/60 p-4 text-xs text-slate-700 leading-relaxed">
                <p class="font-bold uppercase tracking-wider text-amber-800 text-[11px] mb-1">Keuntungan Bayar Online:</p>
                <ul class="list-inside list-disc space-y-1 text-slate-600 text-[11px]">
                    <li>Konfirmasi otomatis 24 jam nonstop.</li>
                    <li>Tanpa perlu repot kirim bukti transfer manual.</li>
                    <li>Status jadwal mobil langsung aman &amp; terkunci.</li>
                </ul>
            </div>
        </div>

        {{-- Form Checkout --}}
        <div class="md:col-span-2">
            <form method="POST" action="{{ route('bookings.pay.process', $booking) }}" class="card p-6 md:p-8 bg-white border border-slate-200 shadow-sm space-y-6">
                @csrf

                {{-- Pilihan Jenis Pembayaran (DP vs Pelunasan) --}}
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 mb-2.5">Pilih Jenis Pembayaran</label>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @if ($booking->paidAmount() == 0 && config('payment.dp_enabled', true))
                            <label class="cursor-pointer rounded-lg bg-slate-50 border-2 border-slate-200 p-4 text-xs transition has-[:checked]:border-[#F5A623] has-[:checked]:bg-[#FFF8EC] hover:border-slate-300">
                                <div class="flex items-start gap-2.5">
                                    <input type="radio" name="payment_type" value="dp" class="mt-1 text-[#F5A623] focus:ring-[#F5A623] border-slate-300" {{ old('payment_type', 'dp') === 'dp' ? 'checked' : '' }} onchange="updateTotalDisplay()">
                                    <div>
                                        <p class="font-black text-slate-900 uppercase tracking-wide">Uang Muka (DP 30%)</p>
                                        <p class="text-[11px] text-slate-600 mt-0.5">Booking tanggal sekarang, sisa dibayar nanti</p>
                                        <p class="mt-2 text-base font-black text-amber-700">{{ rupiah($dpAmount) }}</p>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer rounded-lg bg-slate-50 border-2 border-slate-200 p-4 text-xs transition has-[:checked]:border-[#F5A623] has-[:checked]:bg-[#FFF8EC] hover:border-slate-300">
                                <div class="flex items-start gap-2.5">
                                    <input type="radio" name="payment_type" value="full" class="mt-1 text-[#F5A623] focus:ring-[#F5A623] border-slate-300" {{ old('payment_type') === 'full' ? 'checked' : '' }} onchange="updateTotalDisplay()">
                                    <div>
                                        <p class="font-black text-slate-900 uppercase tracking-wide">Bayar Penuh (100%)</p>
                                        <p class="text-[11px] text-slate-600 mt-0.5">Langsung lunas tanpa sisa tagihan</p>
                                        <p class="mt-2 text-base font-black text-amber-700">{{ rupiah($booking->grandTotal()) }}</p>
                                    </div>
                                </div>
                            </label>
                        @else
                            <label class="cursor-pointer rounded-lg bg-slate-50 border-2 border-slate-200 p-4 text-xs transition has-[:checked]:border-[#F5A623] has-[:checked]:bg-[#FFF8EC] hover:border-slate-300 sm:col-span-2">
                                <div class="flex items-start gap-2.5">
                                    <input type="radio" name="payment_type" value="settlement" class="mt-1 text-[#F5A623] focus:ring-[#F5A623] border-slate-300" checked onchange="updateTotalDisplay()">
                                    <div>
                                        <p class="font-black text-slate-900 uppercase tracking-wide">Pelunasan Sisa Tagihan</p>
                                        <p class="text-[11px] text-slate-600 mt-0.5">Membayar sisa tagihan yang belum terbayar</p>
                                        <p class="mt-2 text-base font-black text-amber-700">{{ rupiah($remaining) }}</p>
                                    </div>
                                </div>
                            </label>
                        @endif
                    </div>
                </div>

                {{-- Pilihan Kanal Pembayaran --}}
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 mb-2.5">Metode Pembayaran</label>
                    <div class="space-y-3">
                        {{-- QRIS --}}
                        <label class="flex items-center justify-between cursor-pointer rounded-lg bg-slate-50 border-2 border-slate-200 p-4 transition has-[:checked]:border-[#F5A623] has-[:checked]:bg-[#FFF8EC] hover:border-slate-300">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="channel" value="qris" class="text-[#F5A623] focus:ring-[#F5A623] border-slate-300" {{ old('channel', 'qris') === 'qris' ? 'checked' : '' }}>
                                <div>
                                    <p class="font-black text-slate-900 text-xs uppercase tracking-wide">QRIS (Semua Pembayaran)</p>
                                    <p class="text-[11px] text-slate-600 mt-0.5">Scan via BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, ShopeePay</p>
                                </div>
                            </div>
                            <span class="badge badge-accent text-[10px] font-black">Instan</span>
                        </label>

                        {{-- Virtual Account Bank --}}
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-700 pt-2">Virtual Account Bank (Otomatis)</p>

                        <div class="grid gap-2 sm:grid-cols-2 text-xs">
                            <label class="flex items-center gap-3 cursor-pointer rounded-lg bg-slate-50 border-2 border-slate-200 p-3 transition has-[:checked]:border-[#F5A623] has-[:checked]:bg-[#FFF8EC] hover:border-slate-300">
                                <input type="radio" name="channel" value="va_bca" class="text-[#F5A623] focus:ring-[#F5A623] border-slate-300" {{ old('channel') === 'va_bca' ? 'checked' : '' }}>
                                <span class="font-bold text-slate-900">BCA Virtual Account</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer rounded-lg bg-slate-50 border-2 border-slate-200 p-3 transition has-[:checked]:border-[#F5A623] has-[:checked]:bg-[#FFF8EC] hover:border-slate-300">
                                <input type="radio" name="channel" value="va_mandiri" class="text-[#F5A623] focus:ring-[#F5A623] border-slate-300" {{ old('channel') === 'va_mandiri' ? 'checked' : '' }}>
                                <span class="font-bold text-slate-900">Mandiri Bill / VA</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer rounded-lg bg-slate-50 border-2 border-slate-200 p-3 transition has-[:checked]:border-[#F5A623] has-[:checked]:bg-[#FFF8EC] hover:border-slate-300">
                                <input type="radio" name="channel" value="va_bni" class="text-[#F5A623] focus:ring-[#F5A623] border-slate-300" {{ old('channel') === 'va_bni' ? 'checked' : '' }}>
                                <span class="font-bold text-slate-900">BNI Virtual Account</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer rounded-lg bg-slate-50 border-2 border-slate-200 p-3 transition has-[:checked]:border-[#F5A623] has-[:checked]:bg-[#FFF8EC] hover:border-slate-300">
                                <input type="radio" name="channel" value="va_bri" class="text-[#F5A623] focus:ring-[#F5A623] border-slate-300" {{ old('channel') === 'va_bri' ? 'checked' : '' }}>
                                <span class="font-bold text-slate-900">BRI BRIVA</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer rounded-lg bg-slate-50 border-2 border-slate-200 p-3 transition has-[:checked]:border-[#F5A623] has-[:checked]:bg-[#FFF8EC] hover:border-slate-300 sm:col-span-2">
                                <input type="radio" name="channel" value="va_permata" class="text-[#F5A623] focus:ring-[#F5A623] border-slate-300" {{ old('channel') === 'va_permata' ? 'checked' : '' }}>
                                <span class="font-bold text-slate-900">Permata Bank Virtual Account</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-5 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Nominal yang akan dibayar:</p>
                        <p id="displayAmount" class="text-2xl font-black text-amber-700 mt-0.5">
                            {{ rupiah($booking->paidAmount() == 0 && config('payment.dp_enabled', true) ? $dpAmount : $remaining) }}
                        </p>
                    </div>
                    <button type="submit" class="btn btn-primary px-6 py-3 font-black uppercase tracking-wider text-xs shadow-md">
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