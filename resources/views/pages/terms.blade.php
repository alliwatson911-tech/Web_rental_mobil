@extends('layouts.app')
@section('title', 'Syarat & Ketentuan Sewa - Padaidi Rent Car')

@section('content')
<x-breadcrumb :crumbs="['Syarat & Ketentuan' => null]" />

<div class="mb-8">
    <span class="section-kicker">KEBIJAKAN RENTAL</span>
    <h1 class="section-title text-slate-900 mb-2">Syarat Sewa</h1>
    <p class="text-slate-600 text-sm max-w-2xl">
        Mohon membaca syarat dan ketentuan sewa berikut demi keamanan, kenyamanan bersama, dan transparansi transaksi. Jika ada pertanyaan khusus, silakan hubungi kami di halaman <a href="{{ route('contact') }}" class="text-amber-600 underline font-semibold hover:text-amber-800">Kontak</a>.
    </p>
</div>

<div class="grid gap-6 md:grid-cols-2">
    {{-- Persyaratan Penyewa --}}
    <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-12 w-12 rounded bg-slate-50 border border-slate-200 flex items-center justify-center text-[#F5A623]">
                <x-icon name="user" class="w-6 h-6" />
            </div>
            <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">Persyaratan penyewa</h2>
        </div>
        <ul class="space-y-3 text-xs text-slate-700 leading-relaxed pt-2 border-t border-slate-200">
            <li class="flex items-start gap-2">
                <x-icon name="check" class="w-4 h-4 text-[#F5A623] shrink-0 mt-0.5" />
                <span>Usia minimal penyewa <strong>{{ $terms['min_age'] }} tahun</strong>.</span>
            </li>
            <li class="flex items-start gap-2">
                <x-icon name="check" class="w-4 h-4 text-[#F5A623] shrink-0 mt-0.5" />
                <span>Wajib memiliki <strong>SIM A</strong> yang masih berlaku minimal <strong>{{ $terms['min_license_years'] }} tahun</strong> (khusus untuk paket sewa lepas kunci).</span>
            </li>
            <li class="flex items-start gap-2">
                <x-icon name="check" class="w-4 h-4 text-[#F5A623] shrink-0 mt-0.5" />
                <span>Menunjukkan KTP elektronik asli dan dokumen pendukung saat pengambilan kendaraan di pool/bandara.</span>
            </li>
        </ul>
    </div>

    {{-- Deposit Jaminan --}}
    <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-12 w-12 rounded bg-slate-50 border border-slate-200 flex items-center justify-center text-[#F5A623]">
                <x-icon name="shield" class="w-6 h-6" />
            </div>
            <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">Deposit</h2>
        </div>
        <div class="text-xs text-slate-700 leading-relaxed pt-2 border-t border-slate-200 space-y-2">
            <p>
                Deposit jaminan sebesar <strong>{{ rupiah($terms['deposit']) }}</strong> dibayarkan saat pengambilan unit mobil untuk keamanan operasional.
            </p>
            <p class="text-slate-500">
                Uang deposit akan dikembalikan 100% secara utuh maksimal 1x24 jam setelah mobil kembali dalam kondisi baik, tanpa denda tilang elektronik (ETLE), dan bensin sesuai posisi awal.
            </p>
        </div>
    </div>

    {{-- Batas Kilometer --}}
    <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-12 w-12 rounded bg-slate-50 border border-slate-200 flex items-center justify-center text-[#F5A623]">
                <x-icon name="car" class="w-6 h-6" />
            </div>
            <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">Batas kilometer</h2>
        </div>
        <div class="text-xs text-slate-700 leading-relaxed pt-2 border-t border-slate-200 space-y-2">
            <p>
                Pemakaian wajar harian dibatasi maksimal <strong>{{ number_format($terms['km_limit_per_day'], 0, ',', '.') }} km per hari</strong> akumulatif selama durasi sewa.
            </p>
            <p class="text-slate-500">
                Kelebihan jarak tempuh di atas kuota dikenakan biaya tambahan sebesar <strong>{{ rupiah($terms['km_over_fee']) }} per km</strong> saat mobil dikembalikan.
            </p>
        </div>
    </div>

    {{-- Keterlambatan --}}
    <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-12 w-12 rounded bg-slate-50 border border-slate-200 flex items-center justify-center text-[#F5A623]">
                <x-icon name="clock" class="w-6 h-6" />
            </div>
            <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">Keterlambatan</h2>
        </div>
        <div class="text-xs text-slate-700 leading-relaxed pt-2 border-t border-slate-200 space-y-2">
            <p>
                Kami memberikan toleransi keterlambatan pengembalian selama <strong>{{ $terms['late_grace_hours'] }} jam</strong> dari jadwal berakhir.
            </p>
            <p class="text-slate-500">
                Setelah masa toleransi lewat, dikenakan denda keterlambatan sebesar <strong>{{ rupiah($terms['late_fee_per_hour']) }} per jam</strong>. Keterlambatan lebih dari 5 jam dihitung tarif sewa 1 hari penuh.
            </p>
        </div>
    </div>

    {{-- Perhitungan Harga & Pembayaran --}}
    <div class="card p-6 md:p-8 bg-white border border-slate-200 shadow-sm md:col-span-2">
        <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide mb-4">Perhitungan harga &amp; pembayaran</h2>
        <ul class="space-y-3 text-xs text-slate-700 leading-relaxed pt-2 border-t border-slate-200">
            <li class="flex items-start gap-2">
                <span class="text-[#F5A623] font-bold">•</span>
                <span>Lama sewa = tanggal kembali dikurangi tanggal ambil (minimal 1 hari). Contoh: ambil 10 Agustus, kembali 12 Agustus = 2 hari sewa penuh.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="text-[#F5A623] font-bold">•</span>
                <span>Total tagihan = (Tarif Harian × Jumlah Hari) + Biaya Tambahan Layanan − Potongan Diskon / Kode Promo.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="text-[#F5A623] font-bold">•</span>
                <span>Pembayaran dapat dilakukan secara otomatis via Payment Gateway (QRIS instan, Virtual Account bank) atau via transfer bank manual ke <strong>{{ $business['bank']['name'] }} {{ $business['bank']['account'] }}</strong> a.n. <strong>{{ $business['bank']['holder'] }}</strong>.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="text-[#F5A623] font-bold">•</span>
                <span>Pesanan yang belum dikonfirmasi dalam <strong>{{ $business['pending_timeout_hours'] }} jam</strong> dan belum ada bukti transfer akan dibatalkan otomatis oleh sistem demi menjaga ketersediaan armada.</span>
            </li>
        </ul>
    </div>

    {{-- Layanan Tambahan (Addons) --}}
    <div class="card p-6 md:p-8 bg-white border border-slate-200 shadow-sm md:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="section-kicker">ADD-ON &amp; EKSTRA</span>
                <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">Layanan tambahan</h2>
            </div>
            <span class="badge badge-accent">Opsi Saat Pemesanan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table-base mt-2">
                <thead>
                    <tr>
                        <th>Tambahan Layanan</th>
                        <th>Tarif</th>
                        <th>Deskripsi Layanan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($addons as $addon)
                        <tr>
                            <td class="font-bold text-slate-900">{{ $addon->name }}</td>
                            <td class="font-extrabold text-amber-700">{{ rupiah($addon->price) }} <span class="text-[11px] font-normal text-slate-500">{{ $addon->chargeLabel() }}</span></td>
                            <td class="text-slate-600 text-xs">{{ $addon->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
