@extends('layouts.admin')
@section('title', 'Lokasi')
@section('actions')<a href="{{ route('admin.locations.create') }}" class="btn btn-primary">+ Tambah lokasi</a>@endsection

@section('content')
<div class="card overflow-x-auto">
    <table class="table-base">
        <thead><tr><th>Nama</th><th>Wajib alamat antar</th><th>Aktif</th><th>Dipakai</th><th></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @foreach ($locations as $location)
            <tr>
                <td class="font-semibold">{{ $location->name }}<br><span class="text-xs font-normal text-slate-500">{{ $location->description }}</span></td>
                <td>{{ $location->requires_address ? 'Ya' : 'Tidak' }}</td>
                <td>{{ $location->is_active ? 'Ya' : 'Tidak' }}</td>
                <td>{{ $location->bookings_count }} pesanan</td>
                <td class="whitespace-nowrap text-right">
                    <a class="btn btn-outline" href="{{ route('admin.locations.edit', $location) }}">Ubah</a>
                    <form method="POST" action="{{ route('admin.locations.destroy', $location) }}" class="inline" data-confirm="Hapus lokasi ini?">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
