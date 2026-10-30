@extends('layouts.admin')
@section('title', $location->exists ? 'Ubah lokasi' : 'Tambah lokasi')

@section('content')
<form method="POST" action="{{ $location->exists ? route('admin.locations.update', $location) : route('admin.locations.store') }}" class="card max-w-xl space-y-4 p-5 md:p-6 bg-[#111827] border border-[#1e293b]">
    @csrf
    @if ($location->exists) @method('PUT') @endif
    <div><label class="label">Nama lokasi</label><input class="input" name="name" value="{{ old('name', $location->name) }}" required></div>
    <div><label class="label">Keterangan</label><input class="input" name="description" value="{{ old('description', $location->description) }}"></div>
    <input type="hidden" name="requires_address" value="0">
    <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" name="requires_address" value="1" @checked(old('requires_address', $location->requires_address))> Pelanggan wajib mengisi alamat antar (biaya antar-jemput otomatis)</label>
    <input type="hidden" name="is_active" value="0">
    <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $location->is_active))> Aktif</label>
    <div class="flex gap-2 pt-2"><button class="btn btn-primary min-h-[44px] px-5 font-black uppercase text-xs">Simpan</button><a href="{{ route('admin.locations.index') }}" class="btn btn-outline min-h-[44px] px-5 font-bold text-xs flex items-center justify-center">Kembali</a></div>
</form>
@endsection
