@extends('layouts.admin')
@section('title', 'Manajemen Member Pelanggan')

@section('content')
<div class="mb-4 flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center">
    <form class="flex flex-wrap items-center gap-2 w-full sm:w-auto" method="GET" action="{{ route('admin.members.index') }}">
        <input class="input max-w-xs text-xs bg-white border-slate-300 text-slate-900" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, no HP..." maxlength="100">
        
        <select name="tier" class="input max-w-[140px] text-xs bg-white border-slate-300 text-slate-900">
            <option value="">Semua Tier</option>
            @foreach ($tiers as $t)
                <option value="{{ $t->slug }}" @selected(request('tier') === $t->slug)>{{ $t->name }}</option>
            @endforeach
        </select>

        <select name="kyc" class="input max-w-[160px] text-xs bg-white border-slate-300 text-slate-900">
            <option value="">Semua Status KYC</option>
            <option value="unverified" @selected(request('kyc') === 'unverified')>Belum Unggah</option>
            <option value="pending" @selected(request('kyc') === 'pending')>Menunggu Verifikasi</option>
            <option value="verified" @selected(request('kyc') === 'verified')>Terverifikasi</option>
            <option value="rejected" @selected(request('kyc') === 'rejected')>Ditolak</option>
        </select>

        <button class="btn btn-primary text-xs font-bold">Filter</button>
        @if (request()->hasAny(['q', 'tier', 'kyc']))
            <a href="{{ route('admin.members.index') }}" class="btn btn-outline text-xs border-slate-300 text-slate-700 hover:bg-slate-100">Reset</a>
        @endif
    </form>

    <div class="text-xs text-slate-600 font-medium">
        Total Member: <strong class="text-slate-900">{{ $members->total() }}</strong> orang
    </div>
</div>

<div class="card overflow-x-auto bg-white border border-slate-200 shadow-sm rounded-xl p-0">
    <table class="table-base text-xs w-full min-w-[1050px]">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Member</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Kontak</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Tier Saat Ini</th>
                <th class="py-3 px-4 text-center font-black text-slate-700 uppercase tracking-wider text-[11px]">Status e-KYC</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Sewa Selesai</th>
                <th class="py-3 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px]">Total Belanja</th>
                <th class="py-3 px-4 text-center font-black text-slate-700 uppercase tracking-wider text-[11px]">Status Akun</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Terdaftar</th>
                <th class="py-3 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px]">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @forelse ($members as $member)
            @php
                $profile = $member->memberProfile;
                $tier = $profile?->effectiveTier();
            @endphp
            <tr class="hover:bg-amber-50/40 transition-colors">
                <td class="py-3 px-4 font-bold text-slate-900">
                    <a href="{{ route('admin.members.show', $member) }}" class="text-amber-700 hover:text-amber-900 hover:underline">
                        {{ $member->name }}
                    </a>
                </td>
                <td class="py-3 px-4">
                    <div class="text-slate-900 font-bold">{{ $member->email }}</div>
                    <div class="text-[11px] text-slate-500 font-medium">{{ $member->phone ?? '-' }}</div>
                </td>
                <td class="py-3 px-4">
                    @if ($tier)
                        <span class="badge {{ $tier->slug === 'gold' ? 'badge-accent' : ($tier->slug === 'silver' ? 'badge-info' : 'bg-slate-100 text-slate-700 border border-slate-300') }} font-bold">
                            {{ $tier->name }} ({{ $tier->discount_percentage }}%)
                        </span>
                        @if ($profile->hasManualTier())
                            <span class="text-[10px] bg-amber-100 text-amber-800 border border-amber-300 px-1.5 py-0.5 rounded ml-1 font-bold" title="{{ $profile->manual_tier_notes }}">
                                Manual
                            </span>
                        @endif
                    @else
                        <span class="text-slate-400">-</span>
                    @endif
                </td>
                <td class="py-3 px-4 text-center">
                    @if ($profile)
                        <span class="badge {{ $profile->kycBadgeClass() }}">
                            {{ $profile->kycStatusLabel() }}
                        </span>
                    @else
                        <span class="badge bg-slate-100 text-slate-600 border border-slate-200">Belum Ada</span>
                    @endif
                </td>
                <td class="py-3 px-4 font-semibold text-slate-800">
                    {{ $profile?->total_completed_rentals ?? 0 }} kali
                </td>
                <td class="py-3 px-4 font-black text-slate-900 text-right">
                    {{ rupiah($profile?->total_spend ?? 0) }}
                </td>
                <td class="py-3 px-4 text-center">
                    @if ($profile && $profile->is_active)
                        <span class="inline-flex items-center gap-1 text-emerald-700 font-bold">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-rose-700 font-bold">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span> Nonaktif
                        </span>
                    @endif
                </td>
                <td class="py-3 px-4 text-slate-600 text-[11px] whitespace-nowrap">
                    {{ tanggal($member->created_at, 'd/m/Y') }}
                </td>
                <td class="py-3 px-4 whitespace-nowrap text-right">
                    <a href="{{ route('admin.members.show', $member) }}" class="btn btn-outline text-xs py-1 px-2.5 font-bold text-slate-700 border-slate-300 hover:bg-slate-100">
                        Detail
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="py-6 text-center text-slate-500 italic bg-slate-50/50">
                    Tidak ada data member yang sesuai filter.
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
