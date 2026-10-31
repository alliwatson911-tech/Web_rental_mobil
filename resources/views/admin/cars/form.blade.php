@extends('layouts.admin')
@section('title', $car->exists ? 'Ubah mobil: '.$car->name : 'Tambah mobil')

@section('actions')
    <a href="{{ route('admin.cars.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Daftar Armada</a>
@endsection

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $car->exists ? route('admin.cars.update', $car) : route('admin.cars.store') }}" class="card space-y-4 p-5 md:p-6 bg-[#111827] border border-[#1e293b]">
    @csrf
    @if ($car->exists) @method('PUT') @endif
    <div class="grid gap-4 md:grid-cols-3">
        <div><label class="label">Nama mobil</label><input class="input" name="name" value="{{ old('name', $car->name) }}" required></div>
        <div><label class="label">Merek</label><input class="input" name="brand" value="{{ old('brand', $car->brand) }}" required></div>
        <div><label class="label">Kategori</label>
            <select class="input" name="category_id" required>
                @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $car->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div><label class="label">Nomor polisi</label><input class="input" name="plate_number" value="{{ old('plate_number', $car->plate_number) }}" required></div>
        <div><label class="label">Tahun</label><input class="input" type="number" name="year" value="{{ old('year', $car->year) }}"></div>
        <div><label class="label">Harga per hari (Rp)</label><input class="input" type="number" min="0" step="1" name="daily_price" value="{{ old('daily_price', $car->daily_price) }}" required></div>
        <div><label class="label">Transmisi</label>
            <select class="input" name="transmission">@foreach (\App\Models\Car::TRANSMISSIONS as $k => $v)<option value="{{ $k }}" @selected(old('transmission', $car->transmission) === $k)>{{ $v }}</option>@endforeach</select>
        </div>
        <div><label class="label">Jumlah kursi</label><input class="input" type="number" name="seats" value="{{ old('seats', $car->seats) }}" required></div>
        <div><label class="label">Bahan bakar</label><input class="input" name="fuel" value="{{ old('fuel', $car->fuel) }}" required></div>
        <div><label class="label">Status</label>
            <select class="input" name="status">@foreach (\App\Models\Car::STATUSES as $k => $v)<option value="{{ $k }}" @selected(old('status', $car->status) === $k)>{{ $v }}</option>@endforeach</select>
        </div>
    </div>
    <div><label class="label">Deskripsi</label><textarea class="input" name="description" rows="3">{{ old('description', $car->description) }}</textarea></div>
    <div><label class="label">Tambah foto (bisa pilih beberapa, maks 4 MB per foto)</label><input class="input" type="file" name="photos[]" multiple accept="image/*"></div>
    <p class="text-xs text-slate-400">Mengubah harga hanya berlaku untuk pesanan baru. Pesanan lama tetap memakai harga saat dipesan.</p>
    <div class="flex gap-2"><button class="btn btn-primary min-h-[44px] px-5 font-black uppercase text-xs">Simpan</button><a href="{{ route('admin.cars.index') }}" class="btn btn-outline min-h-[44px] px-5 font-bold text-xs flex items-center justify-center">Kembali</a></div>
</form>

@if ($car->exists && $car->photos->isNotEmpty())
    <section class="card mt-6 p-5 md:p-6 bg-[#111827] border border-[#1e293b]">
        <h2 class="font-black uppercase text-white tracking-wide text-sm">Foto Galeri Armada</h2>
        <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach ($car->photos as $photo)
                <div class="rounded-lg border border-[#1e293b] bg-[#0b1220] p-2">
                    <img src="{{ $photo->url() }}" class="h-28 w-full rounded object-cover" alt="">
                    <div class="mt-2 flex flex-wrap gap-1">
                        @if ($photo->is_primary)
                            <span class="badge badge-accent text-xs">Utama</span>
                        @else
                            <form method="POST" action="{{ route('admin.cars.photos.primary', [$car, $photo]) }}">@csrf<button class="btn btn-outline px-2 py-1 text-xs">Jadikan utama</button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.cars.photos.destroy', [$car, $photo]) }}" data-confirm="Hapus foto ini?">@csrf @method('DELETE')<button class="btn btn-danger px-2 py-1 text-xs">Hapus</button></form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
@endsection
