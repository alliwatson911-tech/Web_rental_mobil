@extends('layouts.app')
@section('title', 'Syarat Sewa')

@section('content')
<h1 class="text-2xl font-bold text-slate-900">Syarat Sewa</h1>
<p class="mt-1 text-sm text-slate-500">Mohon dibaca sebelum memesan. Pertanyaan? Hubungi kami di halaman <a href="{{ route('contact') }}" class="text-brand-600 underline">Kontak</a>.</p>

<div class="mt-6 grid gap-4 md:grid-cols-2">
    <div class="card p-5">
        <h2 class="font-bold text-slate-900">Persyaratan penyewa</h2>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-700">
            <li>Usia minimal <strong>{{ $terms['min_age'] }} tahun</strong>.</li>
            <li>Memiliki <strong>SIM A</strong> yang masih berlaku, minimal <strong>{{ $terms['min_license_years'] }} tahun</strong> (untuk sewa lepas kunci).</li>
            <li>Menunjukkan KTP asli saat pengambilan mobil.</li>
        </ul>
    </div>
    <div class="card p-5">
        <h2 class="font-bold text-slate-900">Deposit</h2>
        <p class="mt-2 text-sm text-slate-700">Deposit jaminan <strong>{{ rupiah($terms['deposit']) }}</strong> dibayarkan saat pengambilan mobil dan dikembalikan penuh setelah mobil kembali dalam kondisi baik.</p>
    </div>
    <div class="card p-5">
        <h2 class="font-bold text-slate-900">Batas kilometer</h2>
        <p class="mt-2 text-sm text-slate-700">Pemakaian maksimal <strong>{{ number_format($terms['km_limit_per_day'], 0, ',', '.') }} km per hari</strong>. Kelebihan dikenakan <strong>{{ rupiah($terms['km_over_fee']) }} per km</strong>.</p>
    </div>
    <div class="card p-5">
        <h2 class="font-bold text-slate-900">Keterlambatan</h2>
        <p class="mt-2 text-sm text-slate-700">Toleransi keterlambatan {{ $terms['late_grace_hours'] }} jam. Setelah itu dikenakan denda <strong>{{ rupiah($terms['late_fee_per_hour']) }} per jam</strong>.</p>
    </div>
    <div class="card p-5 md:col-span-2">
        <h2 class="font-bold text-slate-900">Perhitungan harga &amp; pembayaran</h2>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-700">
            <li>Lama sewa = tanggal kembali dikurangi tanggal ambil (minimal 1 hari). Contoh: ambil 10 Agustus, kembali 12 Agustus = 2 hari.</li>
            <li>Total = harga per hari × jumlah hari + tambahan − diskon.</li>
            <li>Pembayaran via transfer bank ke <strong>{{ $business['bank']['name'] }} {{ $business['bank']['account'] }}</strong> a.n. <strong>{{ $business['bank']['holder'] }}</strong>, lalu unggah bukti di halaman status pesanan.</li>
            <li>Pesanan yang belum dikonfirmasi dalam <strong>{{ $business['pending_timeout_hours'] }} jam</strong> dan belum ada bukti transfer akan dibatalkan otomatis.</li>
        </ul>
    </div>
    <div class="card p-5 md:col-span-2">
        <h2 class="font-bold text-slate-900">Layanan tambahan</h2>
        <table class="table-base mt-2">
            <thead><tr><th>Tambahan</th><th>Harga</th><th>Keterangan</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($addons as $addon)
                <tr><td class="font-medium">{{ $addon->name }}</td><td>{{ rupiah($addon->price) }} {{ $addon->chargeLabel() }}</td><td>{{ $addon->description }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
