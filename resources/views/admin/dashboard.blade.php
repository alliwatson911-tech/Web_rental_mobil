@extends('layouts.admin')
@section('title', 'Dasbor')

@section('content')
<div class="grid grid-cols-2 gap-3 md:grid-cols-6">
    @foreach (\App\Enums\BookingStatus::cases() as $status)
        <a href="{{ route('admin.bookings.index', ['status' => $status->value]) }}" class="card card-accent-top p-4 bg-[#111827] border border-[#1e293b] hover:border-[#F5A623] transition-all group">
            <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 group-hover:text-[#F5A623] transition-colors">{{ $status->label() }}</p>
            <p class="text-2xl font-black text-white mt-1">{{ $counts[$status->value] ?? 0 }}</p>
        </a>
    @endforeach
    <a href="{{ route('admin.payments.index') }}" class="card card-accent-top p-4 bg-[#111827] border border-[#1e293b] hover:border-[#F5A623] transition-all group">
        <p class="text-[11px] font-extrabold uppercase tracking-wider text-amber-400 group-hover:text-amber-300">Bukti bayar perlu dicek</p>
        <p class="text-2xl font-black text-[#F5A623] mt-1">{{ $pendingPayments }}</p>
    </a>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="card p-6 bg-[#111827] border border-[#1e293b]">
        <div class="flex items-center justify-between pb-3 border-b border-[#1e293b] mb-4">
            <h2 class="font-extrabold text-sm uppercase tracking-wide text-white">Jadwal ambil hari ini (sudah dikonfirmasi)</h2>
            <span class="badge badge-success text-[10px]">Hari Ini</span>
        </div>
        @forelse ($pickupsToday as $b)
            <a href="{{ route('admin.bookings.show', $b) }}" class="mt-2 block rounded bg-[#0b1220] border border-[#1e293b] p-3 text-xs hover:border-[#F5A623] hover:bg-[#1e293b]/50 transition-colors">
                <strong class="font-mono text-[#F5A623]">{{ $b->code }}</strong> · <span class="text-white font-bold">{{ $b->car->name }}</span> · <span class="text-slate-300">{{ $b->customer_name }}</span> · <span class="text-slate-400">{{ $b->pickupLocation->name }}</span>
            </a>
        @empty
            <p class="mt-2 text-xs text-slate-400 italic">Tidak ada jadwal pengambilan armada hari ini.</p>
        @endforelse
    </section>

    <section class="card p-6 bg-[#111827] border border-[#1e293b]">
        <div class="flex items-center justify-between pb-3 border-b border-[#1e293b] mb-4">
            <h2 class="font-extrabold text-sm uppercase tracking-wide text-white">Harus kembali hari ini / terlambat</h2>
            <span class="badge badge-warning text-[10px]">Pengembalian</span>
        </div>
        @forelse ($returnsToday as $b)
            <a href="{{ route('admin.bookings.show', $b) }}" class="mt-2 block rounded bg-[#0b1220] border border-[#1e293b] p-3 text-xs hover:border-[#F5A623] hover:bg-[#1e293b]/50 transition-colors">
                <strong class="font-mono text-[#F5A623]">{{ $b->code }}</strong> · <span class="text-white font-bold">{{ $b->car->name }}</span> · <span class="text-rose-400 font-bold">kembali {{ tanggal($b->return_date) }}</span>
            </a>
        @empty
            <p class="mt-2 text-xs text-slate-400 italic">Tidak ada armada yang harus kembali atau terlambat hari ini.</p>
        @endforelse
    </section>
</div>

<section class="card mt-6 overflow-x-auto bg-[#111827] border border-[#1e293b]">
    <div class="p-6 pb-2">
        <h2 class="font-extrabold text-base uppercase tracking-wide text-white">Pesanan terbaru</h2>
    </div>
    <table class="table-base mt-2 text-xs">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Mobil</th>
                <th>Pelanggan</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#1e293b]">
        @foreach ($latest as $b)
            <tr>
                <td><a class="font-mono font-bold text-[#F5A623] hover:underline" href="{{ route('admin.bookings.show', $b) }}">{{ $b->code }}</a></td>
                <td class="font-bold text-white">{{ $b->car->name }}</td>
                <td class="text-slate-300">{{ $b->customer_name }}</td>
                <td class="text-slate-400">{{ tanggal($b->pickup_date) }} – {{ tanggal($b->return_date) }}</td>
                <td class="font-extrabold text-white">{{ rupiah($b->total) }}</td>
                <td>@include('partials.status-badge', ['status' => $b->status])</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
<p class="mt-4 text-xs text-slate-400">Total armada terdaftar: <strong class="text-white">{{ $carCount }} mobil</strong>.</p>
@endsection
