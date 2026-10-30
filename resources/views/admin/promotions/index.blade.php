@extends('layouts.admin')
@section('title', 'Manajemen Diskon & Promo')
@section('actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.promotions.report') }}" class="btn btn-outline text-xs flex items-center gap-1.5">
            <x-icon name="chart-bar" class="w-4 h-4 text-slate-700" />
            <span>Laporan Pemakaian</span>
        </a>
        <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary text-xs">+ Tambah Promo Baru</a>
    </div>
@endsection

@section('content')
<div class="mb-4 flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center">
    <form class="flex flex-wrap items-center gap-2 w-full sm:w-auto" method="GET" action="{{ route('admin.promotions.index') }}">
        <input class="input max-w-xs text-xs" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode promo...">

        <select name="target_type" class="input max-w-[160px] text-xs">
            <option value="">Semua Tipe Target</option>
            <option value="coupon" @selected(request('target_type') === 'coupon')>Kode Kupon (Voucher)</option>
            <option value="first_order" @selected(request('target_type') === 'first_order')>Pelanggan Baru</option>
            <option value="period" @selected(request('target_type') === 'period')>Musiman / Periode</option>
            <option value="tier" @selected(request('target_type') === 'tier')>Tier Member</option>
        </select>

        <select name="status" class="input max-w-[140px] text-xs">
            <option value="">Semua Status</option>
            <option value="1" @selected(request('status') === '1')>Aktif</option>
            <option value="0" @selected(request('status') === '0')>Nonaktif</option>
        </select>

        <button class="btn btn-outline text-xs">Filter</button>
        @if (request()->hasAny(['q', 'target_type', 'status']))
            <a href="{{ route('admin.promotions.index') }}" class="text-xs text-slate-500 hover:text-slate-800">Reset</a>
        @endif
    </form>

    <div class="text-xs text-slate-500">
        Total Promo: <strong>{{ $promotions->total() }}</strong> entri
    </div>
</div>

<div class="card overflow-x-auto bg-[#111827] border border-[#1e293b]">
    <table class="table-base text-xs">
        <thead>
            <tr>
                <th>Nama Promo</th>
                <th>Kode Voucher</th>
                <th>Target</th>
                <th>Nilai Potongan</th>
                <th>Kuota & Pemakaian</th>
                <th>Syarat Minimal</th>
                <th>Masa Berlaku</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#1e293b]">
        @forelse ($promotions as $promo)
            <tr class="hover:bg-[#1e293b]/50">
                <td class="font-bold text-white">
                    <div>{{ $promo->name }}</div>
                    @if ($promo->description)
                        <div class="text-[11px] text-slate-400 font-normal max-w-xs truncate">{{ $promo->description }}</div>
                    @endif
                </td>
                <td>
                    @if ($promo->code)
                        <span class="font-mono font-bold text-brand-800 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded text-xs">
                            {{ $promo->code }}
                        </span>
                    @else
                        <span class="text-slate-400 italic text-[11px]">Otomatis (Tanpa Kode)</span>
                    @endif
                </td>
                <td>
                    <span class="badge bg-slate-100 text-slate-700">
                        {{ $promo->targetTypeLabel() }}
                    </span>
                </td>
                <td>
                    <span class="font-bold text-emerald-700 text-sm">
                        {{ $promo->formattedValue() }}
                    </span>
                    @if ($promo->type === 'percentage' && $promo->max_discount)
                        <div class="text-[10px] text-slate-500">Maks: {{ rupiah($promo->max_discount) }}</div>
                    @endif
                </td>
                <td>
                    <div class="font-medium text-slate-800">
                        Terpakai: <strong>{{ $promo->used_count }}</strong>
                        @if ($promo->usage_limit)
                            / {{ $promo->usage_limit }}
                        @else
                            <span class="text-slate-400 text-[10px]"> (Tanpa Batas)</span>
                        @endif
                    </div>
                    @if ($promo->usage_limit_per_user)
                        <div class="text-[10px] text-slate-500">Maks. {{ $promo->usage_limit_per_user }}x/user</div>
                    @endif
                </td>
                <td class="text-slate-600">
                    @if ($promo->min_order_amount > 0)
                        <div>Min. {{ rupiah($promo->min_order_amount) }}</div>
                    @endif
                    @if ($promo->min_days > 1)
                        <div>Min. {{ $promo->min_days }} hari</div>
                    @endif
                    @if ($promo->min_order_amount == 0 && $promo->min_days <= 1)
                        <span class="text-slate-400 text-[11px]">Tanpa Syarat</span>
                    @endif
                </td>
                <td class="text-slate-500 text-[11px] whitespace-nowrap">
                    @if ($promo->starts_at || $promo->ends_at)
                        <div>{{ $promo->starts_at ? $promo->starts_at->format('d/m/Y') : 'Sekarang' }}</div>
                        <div class="text-slate-400">s/d {{ $promo->ends_at ? $promo->ends_at->format('d/m/Y') : 'Selamanya' }}</div>
                    @else
                        <span class="text-slate-400">Selalu Aktif</span>
                    @endif
                </td>
                <td>
                    @if ($promo->is_active && ! $promo->isExpired())
                        <span class="badge bg-emerald-100 text-emerald-800 font-semibold">Aktif</span>
                    @elseif ($promo->isExpired())
                        <span class="badge bg-rose-100 text-rose-800">Kadaluwarsa</span>
                    @else
                        <span class="badge bg-slate-200 text-slate-700">Nonaktif</span>
                    @endif
                </td>
                <td class="whitespace-nowrap text-right">
                    <a href="{{ route('admin.promotions.edit', $promo) }}" class="btn btn-outline text-xs py-1 px-2.5">
                        Ubah
                    </a>
                    <form method="POST" action="{{ route('admin.promotions.destroy', $promo) }}" class="inline"
                          data-confirm="Hapus promo {{ $promo->name }}?">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger text-xs py-1 px-2">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="py-8 text-center text-slate-500">
                    Belum ada data diskon atau promo. Klik tombol <strong>+ Tambah Promo Baru</strong> untuk membuatnya.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $promotions->links() }}
</div>
@endsection

