@extends('layouts.admin')
@section('title', 'Jadwal Servis')
@section('actions')<a href="{{ route('admin.blocks.create') }}" class="btn btn-primary min-h-[44px] px-4 font-black uppercase text-xs">+ Tambah jadwal</a>@endsection

@section('content')
<p class="mb-3 text-xs text-slate-600">Selama jadwal ini, mobil tidak muncul di hasil pencarian dan tidak bisa dipesan. Tanggal mulai dan selesai <strong>termasuk</strong> hari tidak tersedia.
    @if (request()->boolean('semua'))<a class="text-amber-700 underline font-bold" href="{{ route('admin.blocks.index') }}">Sembunyikan yang sudah lewat</a>@else<a class="text-amber-700 underline font-bold" href="{{ route('admin.blocks.index', ['semua' => 1]) }}">Tampilkan semua</a>@endif
</p>
<div class="card overflow-x-auto bg-white border border-slate-200 shadow-sm rounded-xl p-0">
    <table class="table-base text-xs w-full min-w-[750px]">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Mobil</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Mulai</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Selesai</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Alasan</th>
                <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Catatan</th>
                <th class="py-3 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px]">Tindakan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @forelse ($blocks as $block)
            <tr class="hover:bg-amber-50/40 transition-colors">
                <td class="py-3 px-4 font-bold text-slate-900">{{ $block->car->name }} <span class="text-xs text-slate-500 font-normal">({{ $block->car->plate_number }})</span></td>
                <td class="py-3 px-4 text-slate-700 font-medium">{{ tanggal($block->start_date, 'd/m/Y') }}</td>
                <td class="py-3 px-4 text-slate-700 font-medium">{{ tanggal($block->end_date, 'd/m/Y') }}</td>
                <td class="py-3 px-4 text-amber-800 font-bold">{{ $block->reason }}</td>
                <td class="py-3 px-4 text-slate-600">{{ $block->notes ?: '-' }}</td>
                <td class="py-3 px-4 whitespace-nowrap text-right space-x-1">
                    <a class="btn btn-outline text-xs px-3 py-1 font-bold text-slate-700 border-slate-300 hover:bg-slate-100" href="{{ route('admin.blocks.edit', $block) }}">Ubah</a>
                    <form method="POST" action="{{ route('admin.blocks.destroy', $block) }}" class="inline" data-confirm="Hapus jadwal ini?">@csrf @method('DELETE')<button class="btn btn-danger text-xs px-3 py-1 font-bold text-white">Hapus</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="py-6 text-center text-slate-500 italic bg-slate-50/50">Belum ada jadwal servis.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $blocks->links() }}</div>
@endsection
