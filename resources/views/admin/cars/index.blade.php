@extends('layouts.admin')
@section('title', 'Armada')
@section('actions')<a href="{{ route('admin.cars.create') }}" class="btn btn-primary">+ Tambah mobil</a>@endsection

@section('content')
<form class="mb-4 flex gap-2"><input class="input max-w-xs" name="q" value="{{ request('q') }}" placeholder="Cari nama / nomor polisi"><button class="btn btn-outline">Cari</button></form>
<div class="card overflow-x-auto">
    <table class="table-base">
        <thead><tr><th>Foto</th><th>Nama</th><th>Kategori</th><th>No. Polisi</th><th>Harga/hari</th><th>Status</th><th></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($cars as $car)
            <tr>
                <td class="w-24">@include('partials.car-image', ['car' => $car, 'class' => 'h-12 w-20 rounded object-cover'])</td>
                <td class="font-semibold">{{ $car->name }}<br><span class="text-xs font-normal text-slate-500">{{ $car->brand }} {{ $car->year }}</span></td>
                <td>{{ $car->category->name }}</td>
                <td>{{ $car->plate_number }}</td>
                <td>{{ rupiah($car->daily_price) }}</td>
                <td><span class="badge {{ $car->status === 'available' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">{{ $car->statusLabel() }}</span></td>
                <td class="whitespace-nowrap text-right">
                    <a href="{{ route('admin.cars.edit', $car) }}" class="btn btn-outline">Ubah</a>
                    <form method="POST" action="{{ route('admin.cars.destroy', $car) }}" class="inline" data-confirm="Hapus mobil {{ $car->name }}?">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="py-6 text-center text-slate-500">Belum ada mobil.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $cars->links() }}</div>
@endsection
