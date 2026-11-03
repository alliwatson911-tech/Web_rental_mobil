@extends('layouts.admin')
@section('title', 'Dasbor')

@section('content')
<div class="grid grid-cols-2 gap-3 md:grid-cols-6">
    @foreach (\App\Enums\BookingStatus::cases() as $status)
        <a href="{{ route('admin.bookings.index', ['status' => $status->value]) }}" class="card card-accent-top p-4 bg-white border border-slate-200 hover:border-amber-400 transition-all shadow-sm group">
            <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 group-hover:text-amber-700 transition-colors">{{ $status->label() }}</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $counts[$status->value] ?? 0 }}</p>
        </a>
    @endforeach
    <a href="{{ route('admin.payments.index') }}" class="card card-accent-top p-4 bg-white border border-slate-200 hover:border-amber-400 transition-all shadow-sm group">
        <p class="text-[11px] font-extrabold uppercase tracking-wider text-amber-700 group-hover:text-amber-800">Bukti bayar perlu dicek</p>
        <p class="text-2xl font-black text-amber-700 mt-1">{{ $pendingPayments }}</p>
    </a>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="card p-6 bg-white border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
            <h2 class="font-extrabold text-sm uppercase tracking-wide text-slate-900">Jadwal ambil hari ini (sudah dikonfirmasi)</h2>
            <span class="badge badge-success text-[10px]">Hari Ini</span>
        </div>
        @forelse ($pickupsToday as $b)
            <a href="{{ route('admin.bookings.show', $b) }}" class="mt-2 block rounded bg-slate-50 border border-slate-200 p-3 text-xs hover:border-amber-400 hover:bg-amber-50/50 transition-colors">
                <strong class="font-mono text-amber-700 font-bold">{{ $b->code }}</strong> · <span class="text-slate-900 font-bold">{{ $b->car->name }}</span> · <span class="text-slate-700">{{ $b->customer_name }}</span> · <span class="text-slate-500">{{ $b->pickupLocation->name }}</span>
            </a>
        @empty
            <p class="mt-2 text-xs text-slate-500 italic">Tidak ada jadwal pengambilan armada hari ini.</p>
        @endforelse
    </section>

    <section class="card p-6 bg-white border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
            <h2 class="font-extrabold text-sm uppercase tracking-wide text-slate-900">Harus kembali hari ini / terlambat</h2>
            <span class="badge badge-warning text-[10px]">Pengembalian</span>
        </div>
        @forelse ($returnsToday as $b)
            <a href="{{ route('admin.bookings.show', $b) }}" class="mt-2 block rounded bg-slate-50 border border-slate-200 p-3 text-xs hover:border-amber-400 hover:bg-amber-50/50 transition-colors">
                <strong class="font-mono text-amber-700 font-bold">{{ $b->code }}</strong> · <span class="text-slate-900 font-bold">{{ $b->car->name }}</span> · <span class="text-rose-700 font-bold">kembali {{ tanggal($b->return_date) }}</span>
            </a>
        @empty
            <p class="mt-2 text-xs text-slate-500 italic">Tidak ada armada yang harus kembali atau terlambat hari ini.</p>
        @endforelse
    </section>
</div>

<section class="card mt-6 overflow-x-auto bg-white border border-slate-200 shadow-sm">
    <div class="p-6 pb-2">
        <h2 class="font-extrabold text-base uppercase tracking-wide text-slate-900">Pesanan terbaru</h2>
    </div>
    <table class="table-base mt-2 text-xs w-full min-w-[750px]">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Kode</th>
                <th class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Mobil</th>
                <th class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Pelanggan</th>
                <th class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Tanggal</th>
                <th class="py-3.5 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px]">Total</th>
                <th class="py-3.5 px-4 text-center font-black text-slate-700 uppercase tracking-wider text-[11px]">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @foreach ($latest as $b)
            <tr class="hover:bg-amber-50/40 transition-colors">
                <td class="py-3.5 px-4"><a class="font-mono font-bold text-amber-700 hover:text-amber-900 hover:underline" href="{{ route('admin.bookings.show', $b) }}">{{ $b->code }}</a></td>
                <td class="py-3.5 px-4 font-bold text-slate-900">{{ $b->car->name }}</td>
                <td class="py-3.5 px-4 text-slate-700">{{ $b->customer_name }}</td>
                <td class="py-3.5 px-4 text-slate-600">{{ tanggal($b->pickup_date, 'd/m/Y') }} – {{ tanggal($b->return_date, 'd/m/Y') }}</td>
                <td class="py-3.5 px-4 text-right font-extrabold text-slate-900">{{ rupiah($b->total) }}</td>
                <td class="py-3.5 px-4 text-center">@include('partials.status-badge', ['status' => $b->status])</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
<p class="mt-4 text-xs text-slate-600">Total armada terdaftar: <strong class="text-slate-900 font-bold">{{ $carCount }} mobil</strong>.</p>
@endsection
