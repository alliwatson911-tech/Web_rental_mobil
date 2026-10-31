@extends('layouts.admin')
@section('title', $block->exists ? 'Ubah jadwal servis' : 'Tambah jadwal servis')

@section('actions')
    <a href="{{ route('admin.blocks.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Jadwal Servis</a>
@endsection

@section('content')
<form method="POST" action="{{ $block->exists ? route('admin.blocks.update', $block) : route('admin.blocks.store') }}" class="card max-w-xl space-y-4 p-5 md:p-6 bg-[#111827] border border-[#1e293b]">
    @csrf
    @if ($block->exists) @method('PUT') @endif
    <div><label class="label">Mobil</label>
        <select class="input" name="car_id" required>
            @foreach ($cars as $car)<option value="{{ $car->id }}" @selected(old('car_id', $block->car_id) == $car->id)>{{ $car->name }} ({{ $car->plate_number }})</option>@endforeach
        </select>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="label">Tanggal mulai</label><input class="input" type="date" name="start_date" value="{{ old('start_date', $block->start_date?->toDateString()) }}" required></div>
        <div><label class="label">Tanggal selesai (termasuk)</label><input class="input" type="date" name="end_date" value="{{ old('end_date', $block->end_date?->toDateString()) }}" required></div>
    </div>
    <div><label class="label">Alasan</label><input class="input" name="reason" value="{{ old('reason', $block->reason) }}" required></div>
    <div><label class="label">Catatan</label><textarea class="input" name="notes" rows="2">{{ old('notes', $block->notes) }}</textarea></div>
    <div class="flex gap-2 pt-2"><button class="btn btn-primary min-h-[44px] px-5 font-black uppercase text-xs">Simpan</button><a href="{{ route('admin.blocks.index') }}" class="btn btn-outline min-h-[44px] px-5 font-bold text-xs flex items-center justify-center">Kembali</a></div>
</form>
@endsection
