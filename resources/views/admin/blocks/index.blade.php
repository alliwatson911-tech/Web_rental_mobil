@extends('layouts.admin')
@section('title', 'Jadwal Servis')
@section('actions')<a href="{{ route('admin.blocks.create') }}" class="btn btn-primary min-h-[44px] px-4 font-black uppercase text-xs">+ Tambah jadwal</a>@endsection

@section('content')
<p class="mb-3 text-xs text-slate-400">Selama jadwal ini, mobil tidak muncul di hasil pencarian dan tidak bisa dipesan. Tanggal mulai dan selesai <strong>termasuk</strong> hari tidak tersedia.
    @if (request()->boolean('semua'))<a class="text-[#F5A623] underline font-bold" href="{{ route('admin.blocks.index') }}">Sembunyikan yang sudah lewat</a>@else<a class="text-[#F5A623] underline font-bold" href="{{ route('admin.blocks.index', ['semua' => 1]) }}">Tampilkan semua</a>@endif
</p>
<div class="card overflow-x-auto bg-[#111827] border border-[#1e293b]">
    <table class="table-base text-xs">
        <thead><tr><th>Mobil</th><th>Mulai</th><th>Selesai</th><th>Alasan</th><th>Catatan</th><th></th></tr></thead>
        <tbody class="divide-y divide-[#1e293b]">
        @forelse ($blocks as $block)
            <tr>
                <td class="font-bold text-white">{{ $block->car->name }} <span class="text-xs text-slate-400 font-normal">({{ $block->car->plate_number }})</span></td>
                <td class="text-slate-300">{{ tanggal($block->start_date) }}</td>
                <td class="text-slate-300">{{ tanggal($block->end_date) }}</td>
                <td class="text-amber-400 font-semibold">{{ $block->reason }}</td>
                <td class="text-slate-400">{{ $block->notes ?: '-' }}</td>
                <td class="whitespace-nowrap text-right space-x-1">
                    <a class="btn btn-outline text-xs px-3 py-1 font-bold" href="{{ route('admin.blocks.edit', $block) }}">Ubah</a>
                    <form method="POST" action="{{ route('admin.blocks.destroy', $block) }}" class="inline" data-confirm="Hapus jadwal ini?">@csrf @method('DELETE')<button class="btn btn-danger text-xs px-3 py-1 font-bold">Hapus</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="py-6 text-center text-slate-400 italic">Belum ada jadwal servis.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $blocks->links() }}</div>
@endsection
