@extends('layouts.admin')
@section('title', 'Dasbor')

@section('content')
<div class="grid grid-cols-2 gap-3 md:grid-cols-6">
    @foreach (\App\Enums\BookingStatus::cases() as $status)
        <a href="{{ route('admin.bookings.index', ['status' => $status->value]) }}" class="card p-4 hover:border-[#F5A623] transition-colors border border-slate-200 bg-white">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $status->label() }}</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $counts[$status->value] ?? 0 }}</p>
        </a>
    @endforeach
    <a href="{{ route('admin.payments.index') }}" class="card p-4 hover:border-[#F5A623] transition-colors border border-slate-200 bg-white">
        <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Bukti bayar perlu dicek</p>
        <p class="text-2xl font-black text-amber-600 mt-1">{{ $pendingPayments }}</p>
    </a>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="card p-6 bg-white border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
            <h2 class="font-extrabold text-sm uppercase tracking-wide text-slate-900">Jadwal ambil hari ini (sudah dikonfirmasi)</h2>
            <span class="badge bg-emerald-100 text-emerald-800 text-[10px]">Hari Ini</span>
        </div>
        @forelse ($pickupsToday as $b)
            <a href="{{ route('admin.bookings.show', $b) }}" class="mt-2 block rounded border border-slate-200 p-3 text-xs hover:border-[#F5A623] hover:bg-slate-50 transition-colors">
                <strong class="font-mono text-slate-900">{{ $b->code }}</strong> · {{ $b->car->name }} · <span class="font-bold text-slate-800">{{ $b->customer_name }}</span> · {{ $b->pickupLocation->name }}
            </a>
        @empty
            <p class="mt-2 text-xs text-slate-400 italic">Tidak ada jadwal pengambilan armada hari ini.</p>
        @endforelse
    </section>

    <section class="card p-6 bg-white border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
            <h2 class="font-extrabold text-sm uppercase tracking-wide text-slate-900">Harus kembali hari ini / terlambat</h2>
            <span class="badge bg-amber-100 text-amber-800 text-[10px]">Pengembalian</span>
        </div>
        @forelse ($returnsToday as $b)
            <a href="{{ route('admin.bookings.show', $b) }}" class="mt-2 block rounded border border-slate-200 p-3 text-xs hover:border-[#F5A623] hover:bg-slate-50 transition-colors">
                <strong class="font-mono text-slate-900">{{ $b->code }}</strong> · {{ $b->car->name }} · <span class="text-rose-600 font-bold">kembali {{ tanggal($b->return_date) }}</span>
            </a>
        @empty
            <p class="mt-2 text-xs text-slate-400 italic">Tidak ada armada yang harus kembali atau terlambat hari ini.</p>
        @endforelse
    </section>
</div>

<section class="card mt-6 overflow-x-auto bg-white border border-slate-200">
    <div class="p-6 pb-2">
        <h2 class="font-extrabold text-base uppercase tracking-wide text-slate-900">Pesanan terbaru</h2>
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
        <tbody class="divide-y divide-slate-100">
        @foreach ($latest as $b)
            <tr>
                <td><a class="font-mono font-bold text-amber-600 hover:underline" href="{{ route('admin.bookings.show', $b) }}">{{ $b->code }}</a></td>
                <td class="font-bold text-slate-800">{{ $b->car->name }}</td>
                <td>{{ $b->customer_name }}</td>
                <td class="text-slate-500">{{ tanggal($b->pickup_date) }} – {{ tanggal($b->return_date) }}</td>
                <td class="font-bold text-slate-900">{{ rupiah($b->total) }}</td>
                <td>@include('partials.status-badge', ['status' => $b->status])</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
<p class="mt-4 text-xs text-slate-500">Total armada terdaftar: <strong>{{ $carCount }} mobil</strong>.</p>
@endsection
