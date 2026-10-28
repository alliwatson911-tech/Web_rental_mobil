@extends('layouts.app')
@section('title', 'Akun Saya')

@section('content')
<div class="space-y-6">
    {{-- Header Profil & Tier --}}
    <div class="card p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-gradient-to-r from-brand-900 to-brand-800 text-white">
        <div class="flex items-center gap-4">
            <div class="grid h-16 w-16 place-items-center rounded-2xl bg-white/10 text-2xl font-black text-amber-300 ring-2 ring-white/20">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold">{{ $user->name }}</h1>
                    <span class="badge {{ $tier->slug === 'gold' ? 'bg-amber-400 text-slate-900 font-extrabold' : ($tier->slug === 'silver' ? 'bg-slate-200 text-slate-800 font-bold' : 'bg-white/20 text-white') }} px-2.5 py-0.5 text-xs">
                        Tier {{ $tier->name }}
                    </span>
                    @if ($profile->hasManualTier())
                        <span class="text-[10px] bg-amber-500/30 text-amber-200 px-2 py-0.5 rounded" title="{{ $profile->manual_tier_notes }}">Manual</span>
                    @endif
                </div>
                <p class="text-xs text-slate-300 mt-1">{{ $user->email }} · {{ $user->phone }}</p>
                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 rounded bg-black/20 px-2 py-0.5">
                        KYC: <strong>{{ $profile->kycStatusLabel() }}</strong>
                    </span>
                    <span class="inline-flex items-center gap-1 rounded bg-black/20 px-2 py-0.5">
                        WhatsApp: <strong>{{ $profile->isPhoneVerified() ? 'Terverifikasi' : 'Belum Verifikasi' }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('member.profile') }}" class="btn btn-outline border-white/30 text-white hover:bg-white/10 text-xs">Ubah Profil</a>
            <a href="{{ route('member.documents') }}" class="btn bg-brand-600 text-white hover:bg-brand-500 text-xs">Kelola Dokumen e-KYC</a>
        </div>
    </div>

    {{-- Grid Status: Tier Progress, Poin, & Dokumen --}}
    <div class="grid gap-6 md:grid-cols-3">
        {{-- Kartu Tier & Manfaat --}}
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-900">Keanggotaan Member</h2>
                <span class="text-xs font-semibold text-brand-700">Diskon {{ $tier->discount_percentage }}%</span>
            </div>
            <p class="mt-1 text-xs text-slate-500">{{ $tier->description ?: 'Tingkat member Padaidi.' }}</p>

            <div class="mt-4 border-t border-slate-100 pt-3">
                <div class="flex justify-between text-xs text-slate-600">
                    <span>Sewa Selesai: <strong>{{ $profile->total_completed_rentals }} kali</strong></span>
                    <span>Total Belanja: <strong>{{ rupiah($profile->total_spend) }}</strong></span>
                </div>

                @if ($nextTier)
                    <div class="mt-3">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-500">Menuju Tier <strong>{{ $nextTier->name }}</strong></span>
                            <span class="font-bold text-brand-700">{{ $rentalsNeeded }} sewa lagi</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-brand-600 h-2 rounded-full transition-all" style="width: {{ $progressPercent }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="mt-3 text-xs text-emerald-700 font-semibold bg-emerald-50 p-2 rounded">
                        ★ Anda telah mencapai tingkat keanggotaan tertinggi (Gold Member)!
                    </p>
                @endif
            </div>
        </div>

        {{-- Kartu Poin Loyalitas --}}
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-900">Poin Loyalitas</h2>
                <span class="text-2xl font-black text-amber-600">{{ number_format($profile->loyalty_points) }}</span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Dapatkan 1 poin setiap kelipatan Rp 100.000 dari transaksi sewa yang telah selesai.</p>

            <div class="mt-4 border-t border-slate-100 pt-3 text-xs text-slate-600 space-y-1">
                <p>Status Poin: <strong class="text-emerald-700">Aktif</strong></p>
                <p>Poin dapat ditukarkan untuk potongan harga sewa mendatang.</p>
            </div>
        </div>

        {{-- Kartu Dokumen e-KYC --}}
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-900">Verifikasi Dokumen (e-KYC)</h2>
                <span class="badge {{ $profile->isKycVerified() ? 'bg-emerald-100 text-emerald-800' : ($profile->kyc_status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }} text-xs">
                    {{ $profile->kycStatusLabel() }}
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Unggah e-KTP dan SIM A untuk sewa lepas kunci tanpa ribet.</p>

            <div class="mt-4 border-t border-slate-100 pt-3 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span>Foto e-KTP:</span>
                    @if ($ktpDoc)
                        <span class="font-semibold text-emerald-700">✓ Terunggah ({{ $ktpDoc->statusLabel() }})</span>
                    @else
                        <span class="text-rose-600">Belum diunggah</span>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <span>Foto SIM A:</span>
                    @if ($simDoc)
                        <span class="font-semibold text-emerald-700">✓ Terunggah ({{ $simDoc->statusLabel() }})</span>
                    @else
                        <span class="text-rose-600">Belum diunggah</span>
                    @endif
                </div>
                <div class="pt-2">
                    <a href="{{ route('member.documents') }}" class="text-brand-600 underline font-semibold">Unggah / Perbarui Dokumen →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Pesanan Terbaru --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-bold text-slate-900">Riwayat Pesanan Saya</h2>
                <p class="text-xs text-slate-500">Daftar pesanan sewa mobil yang terhubung dengan akun member Anda.</p>
            </div>
            <a href="{{ route('member.bookings') }}" class="btn btn-outline text-xs">Lihat Semua Pesanan</a>
        </div>

        @if ($bookings->isEmpty())
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500">
                <p>Belum ada riwayat pesanan sewa.</p>
                <a href="{{ route('cars.index') }}" class="btn btn-primary mt-3 inline-block text-xs">Jelajahi Armada & Pesan Mobil</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-base text-sm">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Mobil</th>
                            <th>Tanggal Sewa</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($bookings as $b)
                            <tr>
                                <td class="font-bold text-slate-900">{{ $b->code }}</td>
                                <td>{{ $b->car->name }}</td>
                                <td>{{ tanggal($b->pickup_date, 'd M Y') }} – {{ tanggal($b->return_date, 'd M Y') }} ({{ $b->days }} hari)</td>
                                <td class="font-semibold">{{ rupiah($b->grandTotal()) }}</td>
                                <td>@include('partials.status-badge', ['status' => $b->status])</td>
                                <td>
                                    <a href="{{ route('bookings.show', $b) }}" class="btn btn-outline px-2.5 py-1 text-xs">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

