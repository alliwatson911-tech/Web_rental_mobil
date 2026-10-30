@extends('layouts.app')
@section('title', 'Akun Saya - Padaidi Rent Car')

@section('content')
<div class="space-y-6">
    <x-breadcrumb :crumbs="['Dashboard Member' => null]" />

    {{-- Header Profil & Tier --}}
    <div class="card card-accent-top p-6 md:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 bg-[#111827] border border-[#1e293b] text-white">
        <div class="flex items-center gap-4">
            <div class="grid h-16 w-16 place-items-center rounded-lg bg-[#0b1220] text-2xl font-black text-[#F5A623] border border-[#F5A623]/30 shadow-inner">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl md:text-2xl font-black uppercase tracking-wide">{{ $user->name }}</h1>
                    <span class="badge {{ $tier->slug === 'gold' ? 'badge-accent font-extrabold' : ($tier->slug === 'silver' ? 'badge-info font-bold' : 'badge bg-slate-800 text-slate-300 border border-slate-700') }} px-2.5 py-0.5 text-xs">
                        Tier {{ $tier->name }}
                    </span>
                    @if ($profile->hasManualTier())
                        <span class="text-[10px] bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded font-mono" title="{{ $profile->manual_tier_notes }}">Manual</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ $user->email }} · {{ $user->phone }}</p>
                <div class="mt-2.5 flex flex-wrap gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 rounded bg-[#0b1220] border border-[#1e293b] px-2.5 py-1 text-slate-300">
                        KYC: <strong class="text-white">{{ $profile->kycStatusLabel() }}</strong>
                    </span>
                    <span class="inline-flex items-center gap-1 rounded bg-[#0b1220] border border-[#1e293b] px-2.5 py-1 text-slate-300">
                        WhatsApp: <strong class="{{ $profile->isPhoneVerified() ? 'text-emerald-400' : 'text-amber-400' }}">{{ $profile->isPhoneVerified() ? 'Terverifikasi' : 'Belum Verifikasi' }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('member.profile') }}" class="btn btn-outline text-xs py-2 px-4 uppercase font-bold tracking-wider">Ubah Profil</a>
            <a href="{{ route('member.documents') }}" class="btn btn-primary text-xs py-2 px-4 uppercase font-black tracking-wider">Kelola Dokumen e-KYC</a>
        </div>
    </div>

    {{-- Grid Status: Tier Progress, Poin, & Dokumen --}}
    <div class="grid gap-6 md:grid-cols-3">
        {{-- Kartu Tier & Manfaat --}}
        <div class="card p-6 bg-[#111827] border border-[#1e293b] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="font-extrabold text-sm uppercase tracking-wide text-white">Keanggotaan Member</h2>
                    <span class="badge badge-accent font-extrabold">Diskon {{ $tier->discount_percentage }}%</span>
                </div>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">{{ $tier->description ?: 'Tingkat member Padaidi.' }}</p>
            </div>

            <div class="mt-5 border-t border-[#1e293b] pt-4">
                <div class="flex justify-between text-xs text-slate-300">
                    <span>Sewa Selesai: <strong class="text-white">{{ $profile->total_completed_rentals }} kali</strong></span>
                    <span>Total Belanja: <strong class="text-white">{{ rupiah($profile->total_spend) }}</strong></span>
                </div>

                @if ($nextTier)
                    <div class="mt-4">
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="text-slate-400">Menuju Tier <strong class="text-[#F5A623]">{{ $nextTier->name }}</strong></span>
                            <span class="font-bold text-[#F5A623]">{{ $rentalsNeeded }} sewa lagi</span>
                        </div>
                        <div class="w-full bg-[#0b1220] h-2 rounded overflow-hidden border border-[#1e293b]">
                            <div class="bg-[#F5A623] h-2 rounded transition-all" style="width: {{ $progressPercent }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="mt-4 text-xs text-amber-300 font-semibold bg-amber-500/10 border border-amber-500/30 p-2.5 rounded flex items-center gap-2">
                        <x-icon name="star" class="w-4 h-4 text-[#F5A623] shrink-0" />
                        <span>Anda telah mencapai tingkat keanggotaan tertinggi (Gold Member)!</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Kartu Poin Loyalitas --}}
        <div class="card p-6 bg-[#111827] border border-[#1e293b] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="font-extrabold text-sm uppercase tracking-wide text-white">Poin Loyalitas</h2>
                    <span class="text-2xl font-black text-[#F5A623] font-mono">{{ number_format($profile->loyalty_points) }}</span>
                </div>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">Dapatkan 1 poin setiap kelipatan Rp 100.000 dari transaksi sewa yang telah selesai.</p>
            </div>

            <div class="mt-5 border-t border-[#1e293b] pt-4 text-xs text-slate-300 space-y-1">
                <p>Status Poin: <strong class="text-emerald-400">Aktif</strong></p>
                <p class="text-slate-400">Poin dapat ditukarkan untuk potongan harga sewa mendatang.</p>
            </div>
        </div>

        {{-- Kartu Dokumen e-KYC --}}
        <div class="card p-6 bg-[#111827] border border-[#1e293b] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="font-extrabold text-sm uppercase tracking-wide text-white">Verifikasi Dokumen (e-KYC)</h2>
                    <span class="badge {{ $profile->isKycVerified() ? 'badge-success' : ($profile->kyc_status === 'pending' ? 'badge-warning' : 'bg-slate-800 text-slate-300 border border-slate-700') }} text-xs">
                        {{ $profile->kycStatusLabel() }}
                    </span>
                </div>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">Unggah e-KTP dan SIM A untuk sewa lepas kunci tanpa ribet.</p>
            </div>

            <div class="mt-5 border-t border-[#1e293b] pt-4 space-y-2.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Foto e-KTP:</span>
                    @if ($ktpDoc)
                        <span class="font-bold text-emerald-400 flex items-center gap-1">
                            <x-icon name="check" class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                            <span>Terunggah ({{ $ktpDoc->statusLabel() }})</span>
                        </span>
                    @else
                        <span class="text-rose-400 font-semibold">Belum diunggah</span>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Foto SIM A:</span>
                    @if ($simDoc)
                        <span class="font-bold text-emerald-400 flex items-center gap-1">
                            <x-icon name="check" class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                            <span>Terunggah ({{ $simDoc->statusLabel() }})</span>
                        </span>
                    @else
                        <span class="text-rose-400 font-semibold">Belum diunggah</span>
                    @endif
                </div>
                <div class="pt-2">
                    <a href="{{ route('member.documents') }}" class="text-amber-400 hover:text-amber-300 underline font-bold">Unggah / Perbarui Dokumen →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Pesanan Terbaru --}}
    <div class="card p-6 md:p-8 bg-[#111827] border border-[#1e293b]">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="font-extrabold text-base uppercase tracking-wide text-white">Riwayat Pesanan Saya</h2>
                <p class="text-xs text-slate-400">Daftar pesanan sewa mobil yang terhubung dengan akun member Anda.</p>
            </div>
            <a href="{{ route('member.bookings') }}" class="btn btn-outline text-xs py-1.5 px-3 uppercase font-bold tracking-wider">Lihat Semua Pesanan</a>
        </div>

        @if ($bookings->isEmpty())
            <div class="rounded border border-[#1e293b] bg-[#0b1220] p-8 text-center text-xs text-slate-400">
                <p>Belum ada riwayat pesanan sewa.</p>
                <a href="{{ route('cars.index') }}" class="btn btn-primary mt-4 inline-block text-xs py-2 px-4 uppercase font-bold">Jelajahi Armada &amp; Pesan Mobil</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-base text-xs">
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
                    <tbody class="divide-y divide-[#1e293b]">
                        @foreach ($bookings as $b)
                            <tr>
                                <td class="font-mono font-bold text-white">{{ $b->code }}</td>
                                <td class="font-bold text-slate-200">{{ $b->car->name }}</td>
                                <td class="text-slate-400">{{ tanggal($b->pickup_date, 'd M Y') }} – {{ tanggal($b->return_date, 'd M Y') }} ({{ $b->days }} hari)</td>
                                <td class="font-black text-[#F5A623]">{{ rupiah($b->grandTotal()) }}</td>
                                <td>@include('partials.status-badge', ['status' => $b->status])</td>
                                <td>
                                    <a href="{{ route('bookings.show', $b) }}" class="btn btn-outline px-3 py-1 text-xs uppercase font-bold">Detail</a>
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
