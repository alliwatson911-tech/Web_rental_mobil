@extends('layouts.admin')
@section('title', 'Pesanan')

@section('content')
<form class="mb-4 flex flex-wrap gap-2">
    <input class="input max-w-xs" name="q" value="{{ request('q') }}" placeholder="Kode / nama / HP">
    <select class="input max-w-xs" name="status">
        <option value="">Semua status</option>
        @foreach (\App\Enums\BookingStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>@endforeach
    </select>
    <button class="btn btn-outline min-h-[44px] px-4 font-bold">Saring</button>
</form>
<div class="card overflow-x-auto bg-[#111827] border border-[#1e293b]">
    <table class="table-base text-xs">
        <thead><tr><th>Kode</th><th>Dibuat</th><th>Mobil</th><th>Pelanggan</th><th>Sewa</th><th>Total</th><th>Status</th></tr></thead>
        <tbody class="divide-y divide-[#1e293b]">
        @forelse ($bookings as $b)
            <tr>
                <td><a class="font-mono font-bold text-[#F5A623] hover:underline" href="{{ route('admin.bookings.show', $b) }}">{{ $b->code }}</a></td>
                <td class="whitespace-nowrap text-slate-400">{{ tanggal($b->created_at, 'd M H:i') }}</td>
                <td class="font-bold text-white">{{ $b->car->name }}</td>
                <td><span class="font-bold text-white">{{ $b->customer_name }}</span><br><span class="text-xs text-slate-400">{{ $b->customer_phone }}</span></td>
                <td class="whitespace-nowrap text-slate-300">{{ tanggal($b->pickup_date) }} – {{ tanggal($b->return_date) }}<br><span class="text-xs text-slate-400">{{ $b->days }} hari · {{ $b->pickupLocation->name }}</span></td>
                <td class="whitespace-nowrap font-extrabold text-white">{{ rupiah($b->total) }}</td>
                <td>@include('partials.status-badge', ['status' => $b->status])</td>
            </tr>
        @empty
            <tr><td colspan="7" class="py-6 text-center text-slate-400 italic">Tidak ada pesanan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $bookings->links() }}</div>
@endsection
