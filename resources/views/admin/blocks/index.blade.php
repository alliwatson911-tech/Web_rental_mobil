@extends('layouts.admin')
@section('title', 'Jadwal Servis')
@section('actions')<a href="{{ route('admin.blocks.create') }}" class="btn btn-primary">+ Tambah jadwal</a>@endsection

@section('content')
<p class="mb-3 text-sm text-slate-600">Selama jadwal ini, mobil tidak muncul di hasil pencarian dan tidak bisa dipesan. Tanggal mulai dan selesai <strong>termasuk</strong> hari tidak tersedia.
    @if (request()->boolean('semua'))<a class="text-brand-600 underline" href="{{ route('admin.blocks.index') }}">Sembunyikan yang sudah lewat</a>@else<a class="text-brand-600 underline" href="{{ route('admin.blocks.index', ['semua' => 1]) }}">Tampilkan semua</a>@endif
</p>
<div class="card overflow-x-auto">
    <table class="table-base">
        <thead><tr><th>Mobil</th><th>Mulai</th><th>Selesai</th><th>Alasan</th><th>Catatan</th><th></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($blocks as $block)
            <tr>
                <td class="font-semibold">{{ $block->car->name }} <span class="text-xs text-slate-500">{{ $block->car->plate_number }}</span></td>
                <td>{{ tanggal($block->start_date) }}</td><td>{{ tanggal($block->end_date) }}</td>
                <td>{{ $block->reason }}</td><td>{{ $block->notes }}</td>
                <td class="whitespace-nowrap text-right">
                    <a class="btn btn-outline" href="{{ route('admin.blocks.edit', $block) }}">Ubah</a>
                    <form method="POST" action="{{ route('admin.blocks.destroy', $block) }}" class="inline" data-confirm="Hapus jadwal ini?">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="py-6 text-center text-slate-500">Belum ada jadwal servis.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $blocks->links() }}</div>
@endsection
