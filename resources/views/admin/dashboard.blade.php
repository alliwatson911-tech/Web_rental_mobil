@extends('layouts.admin')
@section('title', 'Dasbor')

@section('content')
<div class="grid grid-cols-2 gap-3 md:grid-cols-6">
    @foreach (\App\Enums\BookingStatus::cases() as $status)
        <a href="{{ route('admin.bookings.index', ['status' => $status->value]) }}" class="card p-4 hover:border-brand-500">
            <p class="text-xs text-slate-500">{{ $status->label() }}</p>
            <p class="text-2xl font-extrabold">{{ $counts[$status->value] ?? 0 }}</p>
        </a>
    @endforeach
    <a href="{{ route('admin.payments.index') }}" class="card p-4 hover:border-brand-500">
        <p class="text-xs text-slate-500">Bukti bayar perlu dicek</p>
        <p class="text-2xl font-extrabold text-amber-600">{{ $pendingPayments }}</p>
    </a>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="card p-5">
        <h2 class="font-bold">Jadwal ambil hari ini (sudah dikonfirmasi)</h2>
        @forelse ($pickupsToday as $b)
            <a href="{{ route('admin.bookings.show', $b) }}" class="mt-2 block rounded-lg border border-slate-200 p-3 text-sm hover:bg-slate-50"><strong>{{ $b->code }}</strong> · {{ $b->car->name }} · {{ $b->customer_name }} · {{ $b->pickupLocation->name }}</a>
        @empty
            <p class="mt-2 text-sm text-slate-500">Tidak ada.</p>
        @endforelse
    </section>
    <section class="card p-5">
        <h2 class="font-bold">Harus kembali hari ini / terlambat</h2>
        @forelse ($returnsToday as $b)
            <a href="{{ route('admin.bookings.show', $b) }}" class="mt-2 block rounded-lg border border-slate-200 p-3 text-sm hover:bg-slate-50"><strong>{{ $b->code }}</strong> · {{ $b->car->name }} · kembali {{ tanggal($b->return_date) }}</a>
        @empty
            <p class="mt-2 text-sm text-slate-500">Tidak ada.</p>
        @endforelse
    </section>
</div>

<section class="card mt-6 overflow-x-auto">
    <h2 class="p-5 pb-0 font-bold">Pesanan terbaru</h2>
    <table class="table-base mt-3">
        <thead><tr><th>Kode</th><th>Mobil</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @foreach ($latest as $b)
            <tr>
                <td><a class="font-semibold text-brand-600 hover:underline" href="{{ route('admin.bookings.show', $b) }}">{{ $b->code }}</a></td>
                <td>{{ $b->car->name }}</td><td>{{ $b->customer_name }}</td>
                <td>{{ tanggal($b->pickup_date) }} – {{ tanggal($b->return_date) }}</td>
                <td>{{ rupiah($b->total) }}</td>
                <td>@include('partials.status-badge', ['status' => $b->status])</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
<p class="mt-4 text-xs text-slate-500">Total armada: {{ $carCount }} mobil.</p>
@endsection
