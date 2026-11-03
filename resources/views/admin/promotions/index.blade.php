@extends('layouts.admin')
@section('title', 'Manajemen Diskon & Promo')
@section('actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.promotions.report') }}" class="btn btn-outline text-xs flex items-center gap-1.5 border-slate-300 text-slate-700 hover:bg-slate-100 font-bold">
            <x-icon name="chart-bar" class="w-4 h-4 text-amber-600" />
            <span>Laporan Pemakaian</span>
        </a>
        <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary text-xs font-black uppercase shadow-sm">+ Tambah Promo Baru</a>
    </div>
@endsection

@section('content')
<div class="mb-4 flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center">
    <form class="flex flex-wrap items-center gap-2 w-full sm:w-auto" method="GET" action="{{ route('admin.promotions.index') }}">
        <input class="input max-w-xs text-xs bg-white border-slate-300 text-slate-900" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode promo..." maxlength="100">

        <select name="target_type" class="input max-w-[160px] text-xs bg-white border-slate-300 text-slate-900">
            <option value="">Semua Tipe Target</option>
            <option value="coupon" @selected(request('target_type') === 'coupon')>Kode Kupon (Voucher)</option>
            <option value="first_order" @selected(request('target_type') === 'first_order')>Pelanggan Baru</option>
            <option value="period" @selected(request('target_type') === 'period')>Musiman / Periode</option>
            <option value="tier" @selected(request('target_type') === 'tier')>Tier Member</option>
        </select>

        <select name="status" class="input max-w-[140px] text-xs bg-white border-slate-300 text-slate-900">
            <option value="">Semua Status</option>
            <option value="1" @selected(request('status') === '1')>Aktif</option>
            <option value="0" @selected(request('status') === '0')>Nonaktif</option>
        </select>

        <button class="btn btn-primary text-xs font-bold shadow-sm">Filter</button>
        @if (request()->hasAny(['q', 'target_type', 'status']))
            <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline text-xs border-slate-300 text-slate-700 hover:bg-slate-100">Reset</a>
        @endif
    </form>

    <div class="text-xs text-slate-600 font-medium">
        Total Promo: <strong class="text-slate-900">{{ $promotions->total() }}</strong> entri
    </div>
</div>

<div class="card overflow-x-auto bg-white border border-slate-200 shadow-sm rounded-xl p-0">
    <table class="table-base text-xs w-full min-w-[1100px]">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Nama Promo</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Kode Voucher</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Target</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Nilai Potongan</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Kuota & Pemakaian</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Syarat Minimal</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Masa Berlaku</th>
                <th class="py-3 px-4 text-center font-black text-slate-700 uppercase tracking-wider text-[11px]">Status</th>
                <th class="py-3 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px]">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @forelse ($promotions as $promo)
            <tr class="hover:bg-amber-50/40 transition-colors">
                <td class="py-3 px-4 font-bold text-slate-900">
                    <div>{{ $promo->name }}</div>
                    @if ($promo->description)
                        <div class="text-[11px] text-slate-500 font-normal max-w-xs truncate">{{ $promo->description }}</div>
                    @endif
                </td>
                <td class="py-3 px-4">
                    @if ($promo->code)
                        <span class="font-mono font-bold text-amber-800 bg-amber-50 border border-amber-300 px-2 py-0.5 rounded text-xs">
                            {{ $promo->code }}
                        </span>
                    @else
                        <span class="text-slate-500 italic text-[11px]">Otomatis (Tanpa Kode)</span>
                    @endif
                </td>
                <td class="py-3 px-4">
                    <span class="badge bg-slate-100 text-slate-700 border border-slate-300 font-bold">
                        {{ $promo->targetTypeLabel() }}
                    </span>
                </td>
                <td class="py-3 px-4">
                    <span class="font-black text-emerald-700 text-sm">
                        {{ $promo->formattedValue() }}
                    </span>
                    @if ($promo->type === 'percentage' && $promo->max_discount)
                        <div class="text-[10px] text-slate-500 font-medium">Maks: {{ rupiah($promo->max_discount) }}</div>
                    @endif
                </td>
                <td class="py-3 px-4">
                    <div class="font-semibold text-slate-800">
                        Terpakai: <strong class="text-slate-900">{{ $promo->used_count }}</strong>
                        @if ($promo->usage_limit)
                            / {{ $promo->usage_limit }}
                        @else
                            <span class="text-slate-500 text-[10px]"> (Tanpa Batas)</span>
                        @endif
                    </div>
                    @if ($promo->usage_limit_per_user)
                        <div class="text-[10px] text-slate-500 font-medium">Maks. {{ $promo->usage_limit_per_user }}x/user</div>
                    @endif
                </td>
                <td class="py-3 px-4 text-slate-700">
                    @if ($promo->min_order_amount > 0)
                        <div>Min. {{ rupiah($promo->min_order_amount) }}</div>
                    @endif
                    @if ($promo->min_days > 1)
                        <div>Min. {{ $promo->min_days }} hari</div>
                    @endif
                    @if ($promo->min_order_amount == 0 && $promo->min_days <= 1)
                        <span class="text-slate-500 text-[11px]">Tanpa Syarat</span>
                    @endif
                </td>
                <td class="py-3 px-4 text-slate-600 text-[11px] whitespace-nowrap">
                    @if ($promo->starts_at || $promo->ends_at)
                        <div>{{ $promo->starts_at ? $promo->starts_at->format('d/m/Y') : 'Sekarang' }}</div>
                        <div class="text-slate-500">s/d {{ $promo->ends_at ? $promo->ends_at->format('d/m/Y') : 'Selamanya' }}</div>
                    @else
                        <span class="text-slate-500 font-medium">Selalu Aktif</span>
                    @endif
                </td>
                <td class="py-3 px-4 text-center">
                    @if ($promo->is_active && ! $promo->isExpired())
                        <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold">Aktif</span>
                    @elseif ($promo->isExpired())
                        <span class="badge bg-rose-100 text-rose-800 border border-rose-300 font-bold">Kadaluwarsa</span>
                    @else
                        <span class="badge bg-slate-100 text-slate-700 border border-slate-300 font-bold">Nonaktif</span>
                    @endif
                </td>
                <td class="py-3 px-4 whitespace-nowrap text-right">
                    <a href="{{ route('admin.promotions.edit', $promo) }}" class="btn btn-outline text-xs py-1 px-2.5 font-bold border-slate-300 text-slate-700 hover:bg-slate-100">
                        Ubah
                    </a>
                    <form method="POST" action="{{ route('admin.promotions.destroy', $promo) }}" class="inline"
                          data-confirm="Hapus promo {{ $promo->name }}?">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger text-xs py-1 px-2 font-bold text-white shadow-sm">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="py-8 text-center text-slate-500 italic bg-slate-50/50">
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
