@extends('layouts.admin')
@section('title', $addon->exists ? 'Ubah tambahan' : 'Tambah tambahan')

@section('content')
<form method="POST" action="{{ $addon->exists ? route('admin.addons.update', $addon) : route('admin.addons.store') }}" class="card max-w-xl space-y-4 p-5 md:p-6 bg-[#111827] border border-[#1e293b]">
    @csrf
    @if ($addon->exists) @method('PUT') @endif
    <div><label class="label">Kode (huruf kecil, tanpa spasi)</label><input class="input" name="code" value="{{ old('code', $addon->code) }}" required></div>
    <div><label class="label">Nama</label><input class="input" name="name" value="{{ old('name', $addon->name) }}" required></div>
    <div><label class="label">Deskripsi</label><input class="input" name="description" value="{{ old('description', $addon->description) }}"></div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="label">Harga (Rp)</label><input class="input" type="number" min="0" name="price" value="{{ old('price', $addon->price) }}" required></div>
        <div><label class="label">Jenis biaya</label>
            <select class="input" name="charge_type">
                <option value="per_day" @selected(old('charge_type', $addon->charge_type) === 'per_day')>Per hari</option>
                <option value="once" @selected(old('charge_type', $addon->charge_type) === 'once')>Sekali bayar</option>
            </select>
        </div>
    </div>
    <input type="hidden" name="is_active" value="0">
    <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $addon->is_active))> Aktif (tampil di form pemesanan)</label>
    <div class="flex gap-2 pt-2"><button class="btn btn-primary min-h-[44px] px-5 font-black uppercase text-xs">Simpan</button><a href="{{ route('admin.addons.index') }}" class="btn btn-outline min-h-[44px] px-5 font-bold text-xs flex items-center justify-center">Kembali</a></div>
</form>
@endsection
