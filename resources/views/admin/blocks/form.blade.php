@extends('layouts.admin')
@section('title', $block->exists ? 'Ubah jadwal servis' : 'Tambah jadwal servis')

@section('actions')
    <a href="{{ route('admin.blocks.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Jadwal Servis</a>
@endsection

@section('content')
<form method="POST" action="{{ $block->exists ? route('admin.blocks.update', $block) : route('admin.blocks.store') }}" class="card max-w-xl space-y-4 p-5 md:p-6 bg-white border border-slate-200 shadow-sm rounded-xl">
    @csrf
    @if ($block->exists) @method('PUT') @endif
    <div><label class="label text-slate-700 font-bold">Mobil</label>
        <select class="input" name="car_id" required>
            @foreach ($cars as $car)<option value="{{ $car->id }}" @selected(old('car_id', $block->car_id) == $car->id)>{{ $car->name }} ({{ $car->plate_number }})</option>@endforeach
        </select>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="label text-slate-700 font-bold text-xs uppercase tracking-wider mb-1 block" for="block_start_date_display">Tanggal Mulai (dd/mm/yyyy)</label>
            <x-date-picker id="block_start_date" name="start_date" :value="old('start_date', $block->start_date?->toDateString())" required placeholder="dd/mm/yyyy" class="text-xs h-[42px] min-h-[42px] bg-white border border-slate-300 text-slate-900" />
        </div>
        <div>
            <label class="label text-slate-700 font-bold text-xs uppercase tracking-wider mb-1 block" for="block_end_date_display">Tanggal Selesai (dd/mm/yyyy)</label>
            <x-date-picker id="block_end_date" name="end_date" :value="old('end_date', $block->end_date?->toDateString())" required placeholder="dd/mm/yyyy" class="text-xs h-[42px] min-h-[42px] bg-white border border-slate-300 text-slate-900" />
        </div>
    </div>
    <div><label class="label text-slate-700 font-bold">Alasan</label><input class="input" name="reason" value="{{ old('reason', $block->reason) }}" required maxlength="100"></div>
    <div><label class="label text-slate-700 font-bold">Catatan</label><textarea class="input" name="notes" rows="2" maxlength="500">{{ old('notes', $block->notes) }}</textarea></div>
    <div class="flex gap-2 pt-2"><button class="btn btn-primary min-h-[44px] px-5 font-black uppercase text-xs">Simpan</button><a href="{{ route('admin.blocks.index') }}" class="btn btn-outline min-h-[44px] px-5 font-bold text-xs flex items-center justify-center">Kembali</a></div>
</form>
@endsection
