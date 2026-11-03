@extends('layouts.admin')
@section('title', 'Armada')
@section('actions')<a href="{{ route('admin.cars.create') }}" class="btn btn-primary min-h-[44px] px-4 font-black uppercase text-xs">+ Tambah mobil</a>@endsection

@section('content')
<form class="mb-4 flex flex-wrap gap-2">
    <input class="input max-w-xs text-xs bg-white border border-slate-300 text-slate-900" name="q" value="{{ request('q') }}" placeholder="Cari nama / nomor polisi" maxlength="100">
    <button class="btn btn-primary min-h-[44px] px-4 font-bold text-xs">Cari</button>
    @if (request()->has('q'))
        <a href="{{ route('admin.cars.index') }}" class="btn btn-outline min-h-[44px] px-4 font-bold text-xs flex items-center">Reset</a>
    @endif
</form>
<div class="card overflow-x-auto bg-white border border-slate-200 shadow-sm rounded-xl p-0">
    <table class="table-base text-xs w-full min-w-[850px]">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Foto</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Nama</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Kategori</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">No. Polisi</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Harga/hari</th>
                <th class="py-3 px-4 text-center font-black text-slate-700 uppercase tracking-wider text-[11px]">Status</th>
                <th class="py-3 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px]">Tindakan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @forelse ($cars as $car)
            <tr class="hover:bg-amber-50/40 transition-colors">
                <td class="py-3 px-4 w-24">@include('partials.car-image', ['car' => $car, 'class' => 'h-12 w-20 rounded object-cover border border-slate-200'])</td>
                <td class="py-3 px-4 font-bold text-slate-900">{{ $car->name }}<br><span class="text-xs font-normal text-slate-500">{{ $car->brand }} {{ $car->year }}</span></td>
                <td class="py-3 px-4 text-slate-700 font-semibold">{{ $car->category->name }}</td>
                <td class="py-3 px-4 font-mono text-slate-800 font-bold">{{ $car->plate_number }}</td>
                <td class="py-3 px-4 font-black text-amber-800">{{ rupiah($car->daily_price) }}</td>
                <td class="py-3 px-4 text-center"><span class="badge {{ $car->status === 'available' ? 'badge-success' : 'badge-warning' }}">{{ $car->statusLabel() }}</span></td>
                <td class="py-3 px-4 whitespace-nowrap text-right space-x-1">
                    <a href="{{ route('admin.cars.edit', $car) }}" class="btn btn-outline text-xs px-3 py-1 font-bold text-slate-700 border-slate-300 hover:bg-slate-100">Ubah</a>
                    <form method="POST" action="{{ route('admin.cars.destroy', $car) }}" class="inline" data-confirm="Hapus mobil {{ $car->name }}?">@csrf @method('DELETE')<button class="btn btn-danger text-xs px-3 py-1 font-bold text-white">Hapus</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="py-6 text-center text-slate-500 italic bg-slate-50/50">Belum ada mobil.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $cars->links() }}</div>
@endsection
