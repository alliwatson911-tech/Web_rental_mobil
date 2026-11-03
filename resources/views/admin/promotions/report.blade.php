@extends('layouts.admin')
@section('title', 'Laporan Pemakaian Diskon & Promo')
@section('actions')
    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline text-xs border-slate-300 text-slate-700 hover:bg-slate-100 font-bold">&larr; Kembali ke Daftar Promo</a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Ringkasan Metrik Finansial Promo --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-5 border-l-4 border-amber-500 border border-slate-200 bg-white shadow-sm rounded-xl">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Promo Terpakai</span>
            <div class="text-2xl font-black text-slate-900 mt-1">
                {{ number_format($totalRedemptions) }} <span class="text-xs font-semibold text-slate-500">kali transaksi</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Akumulasi seluruh voucher promo yang berhasil ditebus.</p>
        </div>

        <div class="card p-5 border-l-4 border-emerald-500 border border-slate-200 bg-white shadow-sm rounded-xl">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Nilai Potongan Diskon</span>
            <div class="text-2xl font-black text-emerald-700 mt-1">
                {{ rupiah($totalDiscountGiven) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Total subsidi/potongan harga yang dinikmati pelanggan.</p>
        </div>

        <div class="card p-5 border-l-4 border-blue-500 border border-slate-200 bg-white shadow-sm rounded-xl">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Rata-rata Diskon per Pesanan</span>
            <div class="text-2xl font-black text-amber-800 mt-1">
                {{ rupiah($averageDiscount) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Nilai penghematan rata-rata per transaksi promo.</p>
        </div>
    </div>

    {{-- Filter Pencarian & Periode --}}
    <div class="card p-5 border border-slate-200 bg-white shadow-sm rounded-xl">
        <form method="GET" action="{{ route('admin.promotions.report') }}" class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4 text-xs items-end">
            <div class="flex flex-col justify-end">
                <label class="label text-[11px] mb-1.5 text-slate-700 font-extrabold uppercase tracking-wider min-h-[22px] flex items-center" for="report_promo">Pilih Promo</label>
                <select id="report_promo" name="promotion_id" class="input text-xs h-[42px] min-h-[42px] bg-white border border-slate-300 text-slate-900 font-semibold rounded w-full">
                    <option value="">Semua Promo</option>
                    @foreach ($allPromotions as $p)
                        <option value="{{ $p->id }}" @selected(request('promotion_id') == $p->id)>
                            {{ $p->name }} {{ $p->code ? "({$p->code})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col justify-end">
                <label class="label text-[11px] mb-1.5 text-slate-700 font-extrabold uppercase tracking-wider min-h-[22px] flex items-center" for="report_start_date_display">Dari Tanggal (dd/mm/yyyy)</label>
                <x-date-picker id="report_start_date" name="start_date" :value="request('start_date')" placeholder="dd/mm/yyyy" class="text-xs h-[42px] min-h-[42px] bg-white border border-slate-300 text-slate-900" />
            </div>

            <div class="flex flex-col justify-end">
                <label class="label text-[11px] mb-1.5 text-slate-700 font-extrabold uppercase tracking-wider min-h-[22px] flex items-center" for="report_end_date_display">Sampai Tanggal (dd/mm/yyyy)</label>
                <x-date-picker id="report_end_date" name="end_date" :value="request('end_date')" placeholder="dd/mm/yyyy" class="text-xs h-[42px] min-h-[42px] bg-white border border-slate-300 text-slate-900" />
            </div>

            <div class="flex flex-col justify-end">
                <div class="min-h-[22px] mb-1.5"></div>
                <div class="flex items-center gap-2 h-[42px]">
                    <button type="submit" class="btn btn-primary text-xs h-[42px] min-h-[42px] px-4 font-black uppercase tracking-wider flex-1 shadow-sm flex items-center justify-center gap-1.5 text-[#0b1220]">
                        <x-icon name="search" class="w-3.5 h-3.5 text-[#0b1220]" />
                        <span>Terapkan Filter</span>
                    </button>
                    @if (request()->hasAny(['promotion_id', 'start_date', 'end_date']))
                        <a href="{{ route('admin.promotions.report') }}" class="btn btn-outline text-xs h-[42px] min-h-[42px] px-3.5 font-bold uppercase tracking-wider flex items-center justify-center text-slate-700 border-slate-300 hover:bg-slate-100 hover:text-slate-900 shadow-sm">Reset</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Riwayat Penebusan Promo --}}
    <div class="card overflow-x-auto border border-slate-200 bg-white shadow-sm rounded-xl p-0">
        <table class="table-base text-xs w-full min-w-[960px] border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th scope="col" class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[130px]">Waktu Penebusan</th>
                    <th scope="col" class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[160px]">Kode / Nama Promo</th>
                    <th scope="col" class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[130px]">Kode Pesanan</th>
                    <th scope="col" class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[150px]">Pelanggan</th>
                    <th scope="col" class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[170px]">Mobil yang Disewa</th>
                    <th scope="col" class="py-3.5 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[120px]">Nilai Potongan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
            @forelse ($redemptions as $redemption)
                <tr class="hover:bg-amber-50/40 transition-colors">
                    <td class="py-3.5 px-4 whitespace-nowrap align-middle">
                        <span class="font-bold text-slate-900 block text-xs whitespace-nowrap">
                            {{ tanggal($redemption->created_at, 'd/m/Y') }}
                        </span>
                        <span class="text-[11px] text-slate-500 font-mono block mt-0.5 whitespace-nowrap">
                            {{ tanggal($redemption->created_at, 'H:i') }} WITA
                        </span>
                    </td>
                    <td class="py-3.5 px-4 whitespace-nowrap align-middle">
                        <span class="font-mono font-bold text-amber-800 bg-amber-50 border border-amber-300 px-2 py-0.5 rounded text-[11px] whitespace-nowrap">
                            {{ $redemption->promotion?->code ?? '-' }}
                        </span>
                        <div class="text-[11px] text-slate-800 font-bold mt-1 whitespace-nowrap">{{ $redemption->promotion?->name }}</div>
                    </td>
                    <td class="py-3.5 px-4 whitespace-nowrap align-middle">
                        @if ($redemption->booking)
                            <a href="{{ route('admin.bookings.show', $redemption->booking) }}" class="font-mono font-bold text-amber-700 hover:text-amber-900 hover:underline block text-xs whitespace-nowrap">
                                {{ $redemption->booking->code }}
                            </a>
                        @else
                            <span class="text-slate-400 font-bold">-</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 whitespace-nowrap align-middle">
                        <div class="font-bold text-slate-900 text-xs whitespace-nowrap">{{ $redemption->booking?->customer_name ?? $redemption->user?->name ?? 'Tamu' }}</div>
                        <div class="text-[11px] text-slate-500 font-mono mt-0.5 whitespace-nowrap">{{ $redemption->customer_phone ?? $redemption->user?->phone ?? '-' }}</div>
                    </td>
                    <td class="py-3.5 px-4 whitespace-nowrap align-middle text-slate-800 font-medium">
                        @if ($redemption->booking?->car)
                            <span class="font-bold text-slate-900 block whitespace-nowrap">{{ $redemption->booking->car->name }}</span>
                            <span class="text-slate-500 font-mono text-[11px] block mt-0.5 whitespace-nowrap">{{ $redemption->booking->car->plate_number }}</span>
                        @else
                            <span class="text-slate-400 font-bold">-</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 whitespace-nowrap align-middle text-right">
                        <span class="font-black text-emerald-700 text-xs block whitespace-nowrap">
                            -{{ rupiah($redemption->discount_amount) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-slate-500 bg-slate-50/50">
                        <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                            <x-icon name="search" class="w-6 h-6" />
                        </div>
                        <p class="font-bold text-slate-700 text-sm">Tidak Ada Riwayat Penebusan Promo</p>
                        <p class="text-xs text-slate-500 mt-0.5">Belum ada riwayat pemakaian promo yang sesuai dengan kriteria filter.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </div>

    <div class="mt-4">
        {{ $redemptions->links() }}
    </div>
</div>
@endsection
