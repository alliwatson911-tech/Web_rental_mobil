@extends('layouts.admin')
@section('title', 'Tambahan')
@section('actions')<a href="{{ route('admin.addons.create') }}" class="btn btn-primary min-h-[44px] px-4 font-black uppercase text-xs">+ Tambah</a>@endsection

@section('content')
<div class="card overflow-x-auto bg-[#111827] border border-[#1e293b]">
    <table class="table-base text-xs">
        <thead><tr><th>Kode</th><th>Nama</th><th>Harga</th><th>Aktif</th><th></th></tr></thead>
        <tbody class="divide-y divide-[#1e293b]">
        @foreach ($addons as $addon)
            <tr>
                <td><code class="text-[#F5A623] font-bold bg-[#0b1220] px-2 py-0.5 rounded border border-[#1e293b]">{{ $addon->code }}</code></td>
                <td class="font-bold text-white">{{ $addon->name }}<br><span class="text-xs font-normal text-slate-400">{{ $addon->description }}</span></td>
                <td class="font-extrabold text-white">{{ rupiah($addon->price) }} <span class="text-xs text-slate-400 font-normal">{{ $addon->chargeLabel() }}</span></td>
                <td><span class="badge {{ $addon->is_active ? 'badge-success' : 'badge-warning' }}">{{ $addon->is_active ? 'Ya' : 'Tidak' }}</span></td>
                <td class="whitespace-nowrap text-right space-x-1">
                    <a class="btn btn-outline text-xs px-3 py-1 font-bold" href="{{ route('admin.addons.edit', $addon) }}">Ubah</a>
                    <form method="POST" action="{{ route('admin.addons.destroy', $addon) }}" class="inline" data-confirm="Hapus tambahan ini?">@csrf @method('DELETE')<button class="btn btn-danger text-xs px-3 py-1 font-bold">Hapus</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<p class="mt-3 text-xs text-slate-400">Kode <code class="text-amber-400">delivery</code> dipakai otomatis saat pelanggan memilih lokasi "antar ke alamat".</p>
@endsection
