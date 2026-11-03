@extends('layouts.admin')
@section('title', 'Lokasi')
@section('actions')<a href="{{ route('admin.locations.create') }}" class="btn btn-primary min-h-[44px] px-4 font-black uppercase text-xs">+ Tambah lokasi</a>@endsection

@section('content')
<div class="card overflow-x-auto bg-white border border-slate-200 shadow-sm rounded-xl p-0">
    <table class="table-base text-xs w-full">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Nama</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Wajib alamat antar</th>
                <th class="py-3 px-4 text-center font-black text-slate-700 uppercase tracking-wider text-[11px]">Aktif</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Dipakai</th>
                <th class="py-3 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px]">Tindakan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @foreach ($locations as $location)
            <tr class="hover:bg-amber-50/40 transition-colors">
                <td class="py-3 px-4 font-bold text-slate-900">{{ $location->name }}<br><span class="text-xs font-normal text-slate-500">{{ $location->description }}</span></td>
                <td class="py-3 px-4 text-slate-700 font-medium">{{ $location->requires_address ? 'Ya' : 'Tidak' }}</td>
                <td class="py-3 px-4 text-center"><span class="badge {{ $location->is_active ? 'badge-success' : 'badge-warning' }}">{{ $location->is_active ? 'Ya' : 'Tidak' }}</span></td>
                <td class="py-3 px-4 text-slate-800 font-bold">{{ $location->bookings_count }} pesanan</td>
                <td class="py-3 px-4 whitespace-nowrap text-right space-x-1">
                    <a class="btn btn-outline text-xs px-3 py-1 font-bold text-slate-700 border-slate-300 hover:bg-slate-100" href="{{ route('admin.locations.edit', $location) }}">Ubah</a>
                    <form method="POST" action="{{ route('admin.locations.destroy', $location) }}" class="inline" data-confirm="Hapus lokasi ini?">@csrf @method('DELETE')<button class="btn btn-danger text-xs px-3 py-1 font-bold text-white">Hapus</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
