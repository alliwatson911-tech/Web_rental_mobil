@extends('layouts.admin')
@section('title', 'Manajemen Member Pelanggan')

@section('content')
<div class="mb-4 flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center">
    <form class="flex flex-wrap items-center gap-2 w-full sm:w-auto" method="GET" action="{{ route('admin.members.index') }}">
        <input class="input max-w-xs text-xs" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, no HP...">
        
        <select name="tier" class="input max-w-[140px] text-xs">
            <option value="">Semua Tier</option>
            @foreach ($tiers as $t)
                <option value="{{ $t->slug }}" @selected(request('tier') === $t->slug)>{{ $t->name }}</option>
            @endforeach
        </select>

        <select name="kyc" class="input max-w-[160px] text-xs">
            <option value="">Semua Status KYC</option>
            <option value="unverified" @selected(request('kyc') === 'unverified')>Belum Unggah</option>
            <option value="pending" @selected(request('kyc') === 'pending')>Menunggu Verifikasi</option>
            <option value="verified" @selected(request('kyc') === 'verified')>Terverifikasi</option>
            <option value="rejected" @selected(request('kyc') === 'rejected')>Ditolak</option>
        </select>

        <button class="btn btn-outline text-xs">Filter</button>
        @if (request()->hasAny(['q', 'tier', 'kyc']))
            <a href="{{ route('admin.members.index') }}" class="text-xs text-slate-500 hover:text-slate-800">Reset</a>
        @endif
    </form>

    <div class="text-xs text-slate-500">
        Total Member: <strong>{{ $members->total() }}</strong> orang
    </div>
</div>

<div class="card overflow-x-auto bg-[#111827] border border-[#1e293b]">
    <table class="table-base text-xs">
        <thead>
            <tr>
                <th>Member</th>
                <th>Kontak</th>
                <th>Tier Saat Ini</th>
                <th>Status e-KYC</th>
                <th>Sewa Selesai</th>
                <th>Total Belanja</th>
                <th>Status Akun</th>
                <th>Terdaftar</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#1e293b]">
        @forelse ($members as $member)
            @php
                $profile = $member->memberProfile;
                $tier = $profile?->effectiveTier();
            @endphp
            <tr class="hover:bg-[#1e293b]/50">
                <td class="font-bold text-white">
                    <a href="{{ route('admin.members.show', $member) }}" class="text-[#F5A623] hover:underline">
                        {{ $member->name }}
                    </a>
                </td>
                <td>
                    <div class="text-white font-medium">{{ $member->email }}</div>
                    <div class="text-[11px] text-slate-400">{{ $member->phone ?? '-' }}</div>
                </td>
                <td>
                    @if ($tier)
                        <span class="badge {{ $tier->slug === 'gold' ? 'badge-accent' : ($tier->slug === 'silver' ? 'badge-info' : 'bg-[#0b1220] text-slate-300 border border-[#1e293b]') }}">
                            {{ $tier->name }} ({{ $tier->discount_percentage }}%)
                        </span>
                        @if ($profile->hasManualTier())
                            <span class="text-[10px] bg-amber-500/20 text-amber-300 border border-amber-500/30 px-1.5 py-0.5 rounded ml-1" title="{{ $profile->manual_tier_notes }}">
                                Manual
                            </span>
                        @endif
                    @else
                        <span class="text-slate-400">-</span>
                    @endif
                </td>
                <td>
                    @if ($profile)
                        <span class="badge {{ $profile->kycBadgeClass() }}">
                            {{ $profile->kycStatusLabel() }}
                        </span>
                    @else
                        <span class="badge bg-slate-100 text-slate-500">Belum Ada</span>
                    @endif
                </td>
                <td class="font-medium">
                    {{ $profile?->total_completed_rentals ?? 0 }} kali
                </td>
                <td class="font-bold text-white">
                    {{ rupiah($profile?->total_spend ?? 0) }}
                </td>
                <td>
                    @if ($profile && $profile->is_active)
                        <span class="inline-flex items-center gap-1 text-emerald-400 font-bold">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span> Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-rose-400 font-bold">
                            <span class="h-2 w-2 rounded-full bg-rose-400"></span> Nonaktif
                        </span>
                    @endif
                </td>
                <td class="text-slate-400 text-[11px] whitespace-nowrap">
                    {{ $member->created_at->translatedFormat('d M Y') }}
                </td>
                <td class="whitespace-nowrap text-right">
                    <a href="{{ route('admin.members.show', $member) }}" class="btn btn-outline text-xs py-1 px-2.5 font-bold">
                        Kelola
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="py-8 text-center text-slate-400 italic">
                    Tidak ada data member yang cocok dengan filter.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $members->links() }}
</div>
@endsection

