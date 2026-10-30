@extends('layouts.admin')
@section('title', 'Armada')
@section('actions')<a href="{{ route('admin.cars.create') }}" class="btn btn-primary min-h-[44px] px-4 font-black uppercase text-xs">+ Tambah mobil</a>@endsection

@section('content')
<form class="mb-4 flex flex-wrap gap-2"><input class="input max-w-xs" name="q" value="{{ request('q') }}" placeholder="Cari nama / nomor polisi"><button class="btn btn-outline min-h-[44px] px-4 font-bold">Cari</button></form>
<div class="card overflow-x-auto bg-[#111827] border border-[#1e293b]">
    <table class="table-base text-xs">
        <thead><tr><th>Foto</th><th>Nama</th><th>Kategori</th><th>No. Polisi</th><th>Harga/hari</th><th>Status</th><th></th></tr></thead>
        <tbody class="divide-y divide-[#1e293b]">
        @forelse ($cars as $car)
            <tr>
                <td class="w-24">@include('partials.car-image', ['car' => $car, 'class' => 'h-12 w-20 rounded object-cover border border-[#1e293b]'])</td>
                <td class="font-bold text-white">{{ $car->name }}<br><span class="text-xs font-normal text-slate-400">{{ $car->brand }} {{ $car->year }}</span></td>
                <td class="text-slate-300 font-semibold">{{ $car->category->name }}</td>
                <td class="font-mono text-slate-300">{{ $car->plate_number }}</td>
                <td class="font-black text-[#F5A623]">{{ rupiah($car->daily_price) }}</td>
                <td><span class="badge {{ $car->status === 'available' ? 'badge-success' : 'badge-warning' }}">{{ $car->statusLabel() }}</span></td>
                <td class="whitespace-nowrap text-right space-x-1">
                    <a href="{{ route('admin.cars.edit', $car) }}" class="btn btn-outline text-xs px-3 py-1 font-bold">Ubah</a>
                    <form method="POST" action="{{ route('admin.cars.destroy', $car) }}" class="inline" data-confirm="Hapus mobil {{ $car->name }}?">@csrf @method('DELETE')<button class="btn btn-danger text-xs px-3 py-1 font-bold">Hapus</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="py-6 text-center text-slate-400 italic">Belum ada mobil.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $cars->links() }}</div>
@endsection
