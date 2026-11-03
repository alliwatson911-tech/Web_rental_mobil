@extends('layouts.admin')
@section('title', 'Tambahan')
@section('actions')<a href="{{ route('admin.addons.create') }}" class="btn btn-primary min-h-[44px] px-4 font-black uppercase text-xs">+ Tambah</a>@endsection

@section('content')
<div class="card overflow-x-auto bg-white border border-slate-200 shadow-sm rounded-xl p-0">
    <table class="table-base text-xs w-full">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Kode</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Nama</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Harga</th>
                <th class="py-3 px-4 text-center font-black text-slate-700 uppercase tracking-wider text-[11px]">Aktif</th>
                <th class="py-3 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px]">Tindakan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @foreach ($addons as $addon)
            <tr class="hover:bg-amber-50/40 transition-colors">
                <td class="py-3 px-4"><code class="text-amber-800 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">{{ $addon->code }}</code></td>
                <td class="py-3 px-4 font-bold text-slate-900">{{ $addon->name }}<br><span class="text-xs font-normal text-slate-500">{{ $addon->description }}</span></td>
                <td class="py-3 px-4 font-extrabold text-slate-900">{{ rupiah($addon->price) }} <span class="text-xs text-slate-500 font-normal">{{ $addon->chargeLabel() }}</span></td>
                <td class="py-3 px-4 text-center"><span class="badge {{ $addon->is_active ? 'badge-success' : 'badge-warning' }}">{{ $addon->is_active ? 'Ya' : 'Tidak' }}</span></td>
                <td class="py-3 px-4 whitespace-nowrap text-right space-x-1">
                    <a class="btn btn-outline text-xs px-3 py-1 font-bold text-slate-700 border-slate-300 hover:bg-slate-100" href="{{ route('admin.addons.edit', $addon) }}">Ubah</a>
                    <form method="POST" action="{{ route('admin.addons.destroy', $addon) }}" class="inline" data-confirm="Hapus tambahan ini?">@csrf @method('DELETE')<button class="btn btn-danger text-xs px-3 py-1 font-bold text-white">Hapus</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<p class="mt-3 text-xs text-slate-600">Kode <code class="text-amber-800 font-bold bg-amber-50 px-1 py-0.5 rounded border border-amber-200">delivery</code> dipakai otomatis saat pelanggan memilih lokasi "antar ke alamat".</p>
@endsection
