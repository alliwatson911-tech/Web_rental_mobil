@extends('layouts.admin')
@section('title', 'Pesanan')

@section('content')
<form class="mb-4 flex flex-wrap gap-2">
    <input class="input max-w-xs text-xs bg-white border border-slate-300 text-slate-900" name="q" value="{{ request('q') }}" placeholder="Kode / nama / HP" maxlength="100">
    <select class="input max-w-xs text-xs bg-white border border-slate-300 text-slate-900" name="status">
        <option value="">Semua status</option>
        @foreach (\App\Enums\BookingStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>@endforeach
    </select>
    <button class="btn btn-primary min-h-[44px] px-4 font-bold text-xs">Saring</button>
    @if (request()->hasAny(['q', 'status']))
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline min-h-[44px] px-4 font-bold text-xs flex items-center">Reset</a>
    @endif
</form>
<div class="card overflow-x-auto bg-white border border-slate-200 shadow-sm rounded-xl p-0">
    <table class="table-base text-xs w-full min-w-[960px]">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Kode</th>
                <th class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Dibuat</th>
                <th class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Mobil</th>
                <th class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Pelanggan</th>
                <th class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Sewa</th>
                <th class="py-3.5 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px]">Total</th>
                <th class="py-3.5 px-4 text-center font-black text-slate-700 uppercase tracking-wider text-[11px]">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @forelse ($bookings as $b)
            <tr class="hover:bg-amber-50/40 transition-colors">
                <td class="py-3.5 px-4"><a class="font-mono font-bold text-amber-700 hover:text-amber-900 hover:underline" href="{{ route('admin.bookings.show', $b) }}">{{ $b->code }}</a></td>
                <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 font-medium">{{ tanggal($b->created_at, 'd/m/Y H:i') }}</td>
                <td class="py-3.5 px-4 font-bold text-slate-900">{{ $b->car->name }}</td>
                <td class="py-3.5 px-4"><span class="font-bold text-slate-900">{{ $b->customer_name }}</span><br><span class="text-xs text-slate-500">{{ $b->customer_phone }}</span></td>
                <td class="py-3.5 px-4 whitespace-nowrap text-slate-700 font-medium">{{ tanggal($b->pickup_date, 'd/m/Y') }} – {{ tanggal($b->return_date, 'd/m/Y') }}<br><span class="text-xs text-slate-500">{{ $b->days }} hari · {{ $b->pickupLocation->name }}</span></td>
                <td class="py-3.5 px-4 whitespace-nowrap font-extrabold text-slate-900 text-right">{{ rupiah($b->total) }}</td>
                <td class="py-3.5 px-4 text-center">@include('partials.status-badge', ['status' => $b->status])</td>
            </tr>
        @empty
            <tr><td colspan="7" class="py-6 text-center text-slate-500 italic bg-slate-50/50">Tidak ada pesanan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $bookings->links() }}</div>
@endsection
