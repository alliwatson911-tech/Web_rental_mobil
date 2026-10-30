@extends('layouts.admin')
@section('title', 'Detail Member: ' . $member->name)
@section('actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.members.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Daftar</a>
        <form method="POST" action="{{ route('admin.members.status.toggle', $member) }}" class="inline"
              data-confirm="Apakah Anda yakin ingin {{ $profile->is_active ? 'menonaktifkan' : 'mengaktifkan kembali' }} akun member ini?">
            @csrf
            @if ($profile->is_active)
                <button class="btn btn-danger text-xs">Nonaktifkan Akun</button>
            @else
                <button class="btn bg-emerald-600 hover:bg-emerald-700 text-white text-xs">Aktifkan Akun</button>
            @endif
        </form>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header Info Member --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Profil Card --}}
        <div class="card p-5 md:col-span-2">
            <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-4">
                    <div class="grid h-14 w-14 place-items-center rounded-2xl bg-brand-100 text-brand-700 text-2xl font-black">
                        {{ substr($member->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $member->name }}</h2>
                        <p class="text-xs text-slate-500">{{ $member->email }} · {{ $member->phone ?? 'Belum ada nomor HP' }}</p>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <span class="badge {{ $profile->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                Akun {{ $profile->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <span class="badge {{ $profile->isPhoneVerified() ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-600' }}">
                                WhatsApp: {{ $profile->isPhoneVerified() ? 'Terverifikasi' : 'Belum Verifikasi' }}
                            </span>
                            <span class="badge {{ $profile->kycBadgeClass() }}">
                                e-KYC: {{ $profile->kycStatusLabel() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-xs text-slate-400 block">Terdaftar sejak:</span>
                    <span class="text-xs font-semibold text-slate-700">{{ $member->created_at->translatedFormat('d F Y, H:i') }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs">
                <div>
                    <span class="text-slate-400 block">NIK KTP</span>
                    <span class="font-semibold text-slate-800">{{ $profile->nik ?? 'Belum diisi' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Nomor SIM A</span>
                    <span class="font-semibold text-slate-800">{{ $profile->sim_number ?? 'Belum diisi' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Sewa Selesai</span>
                    <span class="font-bold text-brand-700 text-sm">{{ $profile->total_completed_rentals }} kali</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Total Belanja</span>
                    <span class="font-bold text-slate-900 text-sm">{{ rupiah($profile->total_spend) }}</span>
                </div>
            </div>

            @if ($profile->address)
                <div class="mt-4 border-t border-slate-100 pt-3 text-xs">
                    <span class="text-slate-400 block">Alamat Tinggal / Domisili:</span>
                    <p class="text-slate-700 mt-0.5">{{ $profile->address }}</p>
                </div>
            @endif
        </div>

        {{-- Kartu Poin & Ringkasan Tier --}}
        <div class="card p-5 flex flex-col justify-between">
            <div>
                <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Tier Aktif</span>
                @php $tier = $profile->effectiveTier(); @endphp
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-2xl font-black text-slate-900">
                        {{ $tier ? $tier->name : 'Reguler' }}
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded bg-brand-50 text-brand-700">
                        Diskon {{ $tier ? $tier->discount_percentage : 0 }}%
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    @if ($profile->hasManualTier())
                        <span class="text-amber-700 font-medium">Override Manual Aktif.</span> Kalkulasi otomatis diabaikan sementara.
                    @else
                        Otomatis berdasarkan {{ $profile->total_completed_rentals }} transaksi sewa selesai.
                    @endif
                </p>
            </div>

            <div class="border-t border-slate-100 pt-4 mt-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Saldo Poin Loyalitas:</span>
                    <span class="text-lg font-black text-amber-600">{{ number_format($profile->loyalty_points) }} Poin</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">1 poin didapat setiap kelipatan Rp 100.000 sewa selesai.</p>
            </div>
        </div>
    </div>

    {{-- Kelola Tier Member (Manual Override & Reset) --}}
    <div class="card p-5">
        <h3 class="font-bold text-slate-900 text-sm mb-1">Pengaturan Tier Keanggotaan (Manual Override)</h3>
        <p class="text-xs text-slate-500 mb-4">
            Gunakan form ini bila ingin memberikan apresiasi khusus (misal tamu VIP, relasi direksi) atau menurunkan tier dengan alasan tertentu.
        </p>

        @if ($profile->hasManualTier())
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-bold text-amber-900">Saat ini menggunakan Tier Manual: {{ $profile->manualTier?->name }}</p>
                        <p class="text-amber-800 mt-1"><strong>Alasan:</strong> {{ $profile->manual_tier_notes }}</p>
                        <p class="text-[11px] text-amber-700 mt-1">
                            Diatur oleh: {{ $profile->manualTierUpdater?->name ?? 'Admin' }} pada {{ ($profile->manual_tier_updated_at ?? $profile->manual_tier_set_at)?->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('admin.members.tier.reset', $member) }}"
                          data-confirm="Kembalikan tier member ini ke perhitungan otomatis berdasarkan sewa selesai?">
                        @csrf
                        <button class="btn bg-white border border-amber-300 text-amber-900 hover:bg-amber-100 text-xs whitespace-nowrap">
                            Kembalikan ke Otomatis
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.members.tier.update', $member) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="label text-xs">Pilih Tier Baru</label>
                    <select name="tier_id" class="input text-xs" required>
                        @foreach ($tiers as $t)
                            <option value="{{ $t->id }}" @selected($tier && $tier->id === $t->id)>
                                {{ $t->name }} (Diskon {{ $t->discount_percentage }}% - Min {{ $t->min_completed_rentals }} sewa)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="label text-xs">Catatan / Alasan Perubahan Manual <span class="text-rose-500">*</span></label>
                    <input type="text" name="notes" class="input text-xs" required
                           placeholder="Contoh: Tamu VIP direksi instansi rekanan, upgrade khusus, dsb."
                           value="{{ old('notes') }}">
                </div>
            </div>

            <div class="flex justify-end">
                <button class="btn btn-primary text-xs">Simpan Perubahan Tier</button>
            </div>
        </form>
    </div>

    {{-- Verifikasi Dokumen e-KYC (KTP & SIM A) --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Verifikasi Dokumen Identitas (e-KYC)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Berkas tersimpan di storage privat terenkripsi. Seluruh pembukaan berkas dicatat dalam log audit.</p>
            </div>
            <a href="{{ route('admin.members.logs') }}" class="text-xs text-brand-700 hover:underline">
                Lihat Log Akses Dokumen &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @php
                $ktpDoc = $member->documents->firstWhere('type', 'ktp');
                $simDoc = $member->documents->firstWhere('type', 'sim');
            @endphp

            {{-- Kartu KTP --}}
            <div class="rounded-xl border border-slate-200 p-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800 text-xs">e-KTP (Kartu Tanda Penduduk)</span>
                    </div>
                    @if ($ktpDoc)
                        <span class="badge {{ $ktpDoc->statusBadgeClass() }} text-[11px]">
                            {{ $ktpDoc->statusLabel() }}
                        </span>
                    @else
                        <span class="badge bg-slate-100 text-slate-500 text-[11px]">Belum Diunggah</span>
                    @endif
                </div>

                @if ($ktpDoc)
                    <div class="py-3 text-xs space-y-1">
                        <p class="text-slate-500">Diunggah: <span class="text-slate-800 font-medium">{{ $ktpDoc->created_at->translatedFormat('d M Y, H:i') }}</span></p>
                        @if ($ktpDoc->verified_at)
                            <p class="text-slate-500">Diverifikasi: <span class="text-slate-800 font-medium">{{ $ktpDoc->verified_at->translatedFormat('d M Y, H:i') }}</span></p>
                        @endif
                        @if ($ktpDoc->notes)
                            <p class="text-slate-500">Catatan: <span class="text-slate-800">{{ $ktpDoc->notes }}</span></p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                        <a href="{{ route('member.documents.view', $ktpDoc) }}" target="_blank"
                           class="btn btn-outline text-xs py-1 px-3">
                            Buka Foto KTP ↗
                        </a>

                        @if ($ktpDoc->status !== 'verified')
                            <form method="POST" action="{{ route('admin.members.documents.verify', [$member, $ktpDoc]) }}" class="inline">
                                @csrf
                                <button class="btn bg-emerald-600 hover:bg-emerald-700 text-white text-xs py-1 px-3 flex items-center gap-1">
                                    <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                                    <span>Setujui (Sah)</span>
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.members.documents.reject', [$member, $ktpDoc]) }}" class="flex items-center gap-1">
                            @csrf
                            <input type="text" name="notes" placeholder="Alasan tolak (buram/kadaluwarsa)" required class="input text-xs py-1 px-2 w-48">
                            <button class="btn btn-danger text-xs py-1 px-2.5">Tolak</button>
                        </form>
                    </div>
                @else
                    <p class="py-6 text-center text-xs text-slate-400">Member belum mengunggah dokumen e-KTP.</p>
                @endif
            </div>

            {{-- Kartu SIM A --}}
            <div class="rounded-xl border border-slate-200 p-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800 text-xs">SIM A (Surat Izin Mengemudi)</span>
                    </div>
                    @if ($simDoc)
                        <span class="badge {{ $simDoc->statusBadgeClass() }} text-[11px]">
                            {{ $simDoc->statusLabel() }}
                        </span>
                    @else
                        <span class="badge bg-slate-100 text-slate-500 text-[11px]">Belum Diunggah</span>
                    @endif
                </div>

                @if ($simDoc)
                    <div class="py-3 text-xs space-y-1">
                        <p class="text-slate-500">Diunggah: <span class="text-slate-800 font-medium">{{ $simDoc->created_at->translatedFormat('d M Y, H:i') }}</span></p>
                        @if ($simDoc->verified_at)
                            <p class="text-slate-500">Diverifikasi: <span class="text-slate-800 font-medium">{{ $simDoc->verified_at->translatedFormat('d M Y, H:i') }}</span></p>
                        @endif
                        @if ($simDoc->notes)
                            <p class="text-slate-500">Catatan: <span class="text-slate-800">{{ $simDoc->notes }}</span></p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                        <a href="{{ route('member.documents.view', $simDoc) }}" target="_blank"
                           class="btn btn-outline text-xs py-1 px-3">
                            Buka Foto SIM A ↗
                        </a>

                        @if ($simDoc->status !== 'verified')
                            <form method="POST" action="{{ route('admin.members.documents.verify', [$member, $simDoc]) }}" class="inline">
                                @csrf
                                <button class="btn bg-emerald-600 hover:bg-emerald-700 text-white text-xs py-1 px-3 flex items-center gap-1">
                                    <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                                    <span>Setujui (Sah)</span>
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.members.documents.reject', [$member, $simDoc]) }}" class="flex items-center gap-1">
                            @csrf
                            <input type="text" name="notes" placeholder="Alasan tolak (buram/kadaluwarsa)" required class="input text-xs py-1 px-2 w-48">
                            <button class="btn btn-danger text-xs py-1 px-2.5">Tolak</button>
                        </form>
                    </div>
                @else
                    <p class="py-6 text-center text-xs text-slate-400">Member belum mengunggah dokumen SIM A.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Riwayat Transaksi Sewa Terakhir --}}
    <div class="card p-5">
        <h3 class="font-bold text-slate-900 text-sm mb-3">10 Riwayat Pemesanan Terakhir</h3>
        <div class="overflow-x-auto">
            <table class="table-base text-xs">
                <thead>
                    <tr>
                        <th>Kode Pesanan</th>
                        <th>Mobil</th>
                        <th>Periode</th>
                        <th>Status Sewa</th>
                        <th>Status Bayar</th>
                        <th>Grand Total</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($member->memberBookings as $b)
                    <tr>
                        <td class="font-mono font-bold text-slate-800">{{ $b->booking_code }}</td>
                        <td>{{ $b->car?->brand }} {{ $b->car?->model }} ({{ $b->car?->license_plate }})</td>
                        <td>{{ $b->start_date->format('d/m/Y') }} - {{ $b->end_date->format('d/m/Y') }} ({{ $b->total_days }} hari)</td>
                        <td><span class="badge {{ $b->status->badgeClass() }}">{{ $b->status->label() }}</span></td>
                        <td><span class="badge {{ $b->payment_status->badgeClass() }}">{{ $b->payment_status->label() }}</span></td>
                        <td class="font-bold text-slate-900">{{ rupiah($b->grand_total) }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.bookings.show', $b) }}" class="btn btn-outline text-xs py-0.5 px-2">
                                Lihat
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500">Member ini belum memiliki riwayat pesanan sewa.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Riwayat Pergerakan Poin Loyalitas --}}
    <div class="card p-5">
        <h3 class="font-bold text-slate-900 text-sm mb-3">10 Pergerakan Poin Loyalitas Terakhir</h3>
        <div class="overflow-x-auto">
            <table class="table-base text-xs">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Perubahan Poin</th>
                        <th>Saldo Akhir</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($member->loyaltyLedgers as $log)
                    <tr>
                        <td class="text-slate-500 whitespace-nowrap">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</td>
                        <td class="font-bold {{ $log->points >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $log->points >= 0 ? '+' : '' }}{{ number_format($log->points) }}
                        </td>
                        <td class="font-bold text-slate-800">{{ number_format($log->balance_after) }}</td>
                        <td class="text-slate-600">{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-slate-500">Belum ada riwayat perolehan/pengurangan poin.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

