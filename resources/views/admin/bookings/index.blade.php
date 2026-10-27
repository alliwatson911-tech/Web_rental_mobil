@extends('layouts.admin')
@section('title', 'Pesanan')

@section('content')
<form class="mb-4 flex flex-wrap gap-2">
    <input class="input max-w-xs" name="q" value="{{ request('q') }}" placeholder="Kode / nama / HP">
    <select class="input max-w-xs" name="status">
        <option value="">Semua status</option>
        @foreach (\App\Enums\BookingStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>@endforeach
    </select>
    <button class="btn btn-outline">Saring</button>
</form>
<div class="card overflow-x-auto">
    <table class="table-base">
        <thead><tr><th>Kode</th><th>Dibuat</th><th>Mobil</th><th>Pelanggan</th><th>Sewa</th><th>Total</th><th>Status</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($bookings as $b)
            <tr>
                <td><a class="font-semibold text-brand-600 hover:underline" href="{{ route('admin.bookings.show', $b) }}">{{ $b->code }}</a></td>
                <td class="whitespace-nowrap">{{ tanggal($b->created_at, 'd M H:i') }}</td>
                <td>{{ $b->car->name }}</td>
                <td>{{ $b->customer_name }}<br><span class="text-xs text-slate-500">{{ $b->customer_phone }}</span></td>
                <td class="whitespace-nowrap">{{ tanggal($b->pickup_date) }} – {{ tanggal($b->return_date) }}<br><span class="text-xs text-slate-500">{{ $b->days }} hari · {{ $b->pickupLocation->name }}</span></td>
                <td class="whitespace-nowrap">{{ rupiah($b->total) }}</td>
                <td>@include('partials.status-badge', ['status' => $b->status])</td>
            </tr>
        @empty
            <tr><td colspan="7" class="py-6 text-center text-slate-500">Tidak ada pesanan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $bookings->links() }}</div>
@endsection
