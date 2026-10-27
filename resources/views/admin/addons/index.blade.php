@extends('layouts.admin')
@section('title', 'Tambahan')
@section('actions')<a href="{{ route('admin.addons.create') }}" class="btn btn-primary">+ Tambah</a>@endsection

@section('content')
<div class="card overflow-x-auto">
    <table class="table-base">
        <thead><tr><th>Kode</th><th>Nama</th><th>Harga</th><th>Aktif</th><th></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @foreach ($addons as $addon)
            <tr>
                <td><code>{{ $addon->code }}</code></td>
                <td class="font-semibold">{{ $addon->name }}<br><span class="text-xs font-normal text-slate-500">{{ $addon->description }}</span></td>
                <td>{{ rupiah($addon->price) }} {{ $addon->chargeLabel() }}</td>
                <td>{{ $addon->is_active ? 'Ya' : 'Tidak' }}</td>
                <td class="whitespace-nowrap text-right">
                    <a class="btn btn-outline" href="{{ route('admin.addons.edit', $addon) }}">Ubah</a>
                    <form method="POST" action="{{ route('admin.addons.destroy', $addon) }}" class="inline" data-confirm="Hapus tambahan ini?">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<p class="mt-3 text-xs text-slate-500">Kode <code>delivery</code> dipakai otomatis saat pelanggan memilih lokasi "antar ke alamat".</p>
@endsection
