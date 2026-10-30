@extends('layouts.admin')
@section('title', 'Lokasi')
@section('actions')<a href="{{ route('admin.locations.create') }}" class="btn btn-primary min-h-[44px] px-4 font-black uppercase text-xs">+ Tambah lokasi</a>@endsection

@section('content')
<div class="card overflow-x-auto bg-[#111827] border border-[#1e293b]">
    <table class="table-base text-xs">
        <thead><tr><th>Nama</th><th>Wajib alamat antar</th><th>Aktif</th><th>Dipakai</th><th></th></tr></thead>
        <tbody class="divide-y divide-[#1e293b]">
        @foreach ($locations as $location)
            <tr>
                <td class="font-bold text-white">{{ $location->name }}<br><span class="text-xs font-normal text-slate-400">{{ $location->description }}</span></td>
                <td class="text-slate-300">{{ $location->requires_address ? 'Ya' : 'Tidak' }}</td>
                <td><span class="badge {{ $location->is_active ? 'badge-success' : 'badge-warning' }}">{{ $location->is_active ? 'Ya' : 'Tidak' }}</span></td>
                <td class="text-slate-300 font-semibold">{{ $location->bookings_count }} pesanan</td>
                <td class="whitespace-nowrap text-right space-x-1">
                    <a class="btn btn-outline text-xs px-3 py-1 font-bold" href="{{ route('admin.locations.edit', $location) }}">Ubah</a>
                    <form method="POST" action="{{ route('admin.locations.destroy', $location) }}" class="inline" data-confirm="Hapus lokasi ini?">@csrf @method('DELETE')<button class="btn btn-danger text-xs px-3 py-1 font-bold">Hapus</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
