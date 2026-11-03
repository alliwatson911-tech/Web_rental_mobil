@extends('layouts.admin')
@section('title', $location->exists ? 'Ubah lokasi' : 'Tambah lokasi')

@section('actions')
    <a href="{{ route('admin.locations.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Daftar Lokasi</a>
@endsection

@section('content')
<form method="POST" action="{{ $location->exists ? route('admin.locations.update', $location) : route('admin.locations.store') }}" class="card max-w-xl space-y-4 p-5 md:p-6 bg-white border border-slate-200 shadow-sm rounded-xl">
    @csrf
    @if ($location->exists) @method('PUT') @endif
    <div><label class="label text-slate-700 font-bold">Nama lokasi</label><input class="input bg-white border-slate-300 text-slate-900" name="name" value="{{ old('name', $location->name) }}" required maxlength="100"></div>
    <div><label class="label text-slate-700 font-bold">Keterangan</label><input class="input bg-white border-slate-300 text-slate-900" name="description" value="{{ old('description', $location->description) }}" maxlength="500"></div>
    <input type="hidden" name="requires_address" value="0">
    <label class="flex items-center gap-2 text-sm text-slate-700 font-medium"><input type="checkbox" name="requires_address" value="1" @checked(old('requires_address', $location->requires_address))> Pelanggan wajib mengisi alamat antar (biaya antar-jemput otomatis)</label>
    <input type="hidden" name="is_active" value="0">
    <label class="flex items-center gap-2 text-sm text-slate-700 font-medium"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $location->is_active))> Aktif</label>
    <div class="flex gap-2 pt-2"><button class="btn btn-primary min-h-[44px] px-5 font-black uppercase text-xs">Simpan</button><a href="{{ route('admin.locations.index') }}" class="btn btn-outline min-h-[44px] px-5 font-bold text-xs flex items-center justify-center">Kembali</a></div>
</form>
@endsection
