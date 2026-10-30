@extends('layouts.admin')
@section('title', 'Laporan Pemakaian Diskon & Promo')
@section('actions')
    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Daftar Promo</a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Ringkasan Metrik Finansial Promo --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-5 border-l-4 border-[#F5A623] border border-[#1e293b] bg-[#111827]">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Promo Terpakai</span>
            <div class="text-2xl font-black text-white mt-1">
                {{ number_format($totalRedemptions) }} <span class="text-xs font-normal text-slate-400">kali transaksi</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Akumulasi seluruh voucher promo yang berhasil ditebus.</p>
        </div>

        <div class="card p-5 border-l-4 border-emerald-500 border border-[#1e293b] bg-[#111827]">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Nilai Potongan Diskon</span>
            <div class="text-2xl font-black text-emerald-400 mt-1">
                {{ rupiah($totalDiscountGiven) }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Total subsidi/potongan harga yang dinikmati pelanggan.</p>
        </div>

        <div class="card p-5 border-l-4 border-amber-500 border border-[#1e293b] bg-[#111827]">
            <span class="text-xs text-slate-400 font-semibold uppercase">Rata-rata Diskon per Pesanan</span>
            <div class="text-2xl font-black text-[#F5A623] mt-1">
                {{ rupiah($averageDiscount) }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Nilai penghematan rata-rata per transaksi promo.</p>
        </div>
    </div>

    {{-- Filter Pencarian & Periode --}}
    <div class="card p-4 border border-[#1e293b] bg-[#111827]">
        <form method="GET" action="{{ route('admin.promotions.report') }}" class="flex flex-wrap items-end gap-3 text-xs">
            <div>
                <label class="label text-[11px] mb-1 text-slate-300">Pilih Promo</label>
                <select name="promotion_id" class="input text-xs py-1.5 min-w-[200px]">
                    <option value="">Semua Promo</option>
                    @foreach ($allPromotions as $p)
                        <option value="{{ $p->id }}" @selected(request('promotion_id') == $p->id)>
                            {{ $p->name }} {{ $p->code ? "({$p->code})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label text-[11px] mb-1 text-slate-300">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="input text-xs py-1.5">
            </div>

            <div>
                <label class="label text-[11px] mb-1 text-slate-300">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="input text-xs py-1.5">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-outline text-xs py-1.5">Terapkan Filter</button>
                @if (request()->hasAny(['promotion_id', 'start_date', 'end_date']))
                    <a href="{{ route('admin.promotions.report') }}" class="text-xs text-slate-500 hover:text-slate-800">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Riwayat Penebusan Promo --}}
    <div class="card overflow-x-auto border border-[#1e293b] bg-[#111827]">
        <table class="table-base text-xs">
            <thead>
                <tr>
                    <th>Waktu Penebusan</th>
                    <th>Kode / Nama Promo</th>
                    <th>Kode Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Mobil yang Disewa</th>
                    <th>Nilai Potongan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e293b]">
            @forelse ($redemptions as $redemption)
                <tr class="hover:bg-[#1e293b]/50 transition">
                    <td class="font-mono text-slate-400 whitespace-nowrap text-[11px]">
                        {{ $redemption->created_at->translatedFormat('d M Y, H:i') }}
                    </td>
                    <td>
                        <span class="font-mono font-bold text-[#F5A623] bg-[#0b1220] border border-[#1e293b] px-2 py-0.5 rounded text-[11px]">
                            {{ $redemption->promotion?->code ?? '-' }}
                        </span>
                        <div class="text-[11px] text-slate-300 mt-0.5">{{ $redemption->promotion?->name }}</div>
                    </td>
                    <td>
                        @if ($redemption->booking)
                            <a href="{{ route('admin.bookings.show', $redemption->booking) }}" class="font-mono font-bold text-[#F5A623] hover:underline">
                                {{ $redemption->booking->code }}
                            </a>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="font-medium text-white">{{ $redemption->booking?->customer_name ?? $redemption->user?->name ?? 'Tamu' }}</div>
                        <div class="text-[11px] text-slate-400">{{ $redemption->customer_phone ?? $redemption->user?->phone ?? '-' }}</div>
                    </td>
                    <td class="text-slate-200">
                        @if ($redemption->booking?->car)
                            {{ $redemption->booking->car->brand }} {{ $redemption->booking->car->model }}
                            <span class="text-slate-400 text-[11px]">({{ $redemption->booking->car->license_plate }})</span>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="font-bold text-emerald-400 text-sm">
                            -{{ rupiah($redemption->discount_amount) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-400">
                        Belum ada riwayat pemakaian promo yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $redemptions->links() }}
    </div>
</div>
@endsection

