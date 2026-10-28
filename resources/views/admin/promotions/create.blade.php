@extends('layouts.admin')
@section('title', 'Tambah Diskon / Promo Baru')
@section('actions')
    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Daftar</a>
@endsection

@section('content')
<div class="card p-6 max-w-4xl">
    <form method="POST" action="{{ route('admin.promotions.store') }}" class="space-y-6">
        @csrf

        {{-- Bagian 1: Identitas Promo --}}
        <div>
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">Informasi Utama</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label text-xs">Nama Promo <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" class="input text-xs" required
                           placeholder="Contoh: Promo Akhir Pekan Hemat" value="{{ old('name') }}">
                    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label text-xs">Kode Voucher (Kosongkan bila otomatis)</label>
                    <input type="text" name="code" class="input text-xs font-mono uppercase"
                           placeholder="Contoh: HEMAT50K" value="{{ old('code') }}">
                    @error('code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika ingin promo diterapkan otomatis tanpa input kode kupon.</p>
                </div>

                <div>
                    <label class="label text-xs">Tipe Target Promo <span class="text-rose-500">*</span></label>
                    <select name="target_type" class="input text-xs" required>
                        <option value="coupon" @selected(old('target_type') === 'coupon')>Kode Voucher (Kupon input manual)</option>
                        <option value="first_order" @selected(old('target_type') === 'first_order')>Pelanggan Baru (Sewa Pertama Kali)</option>
                        <option value="period" @selected(old('target_type') === 'period')>Musiman / Periode Tertentu</option>
                        <option value="tier" @selected(old('target_type') === 'tier')>Diskon Tingkat Member</option>
                    </select>
                </div>

                <div>
                    <label class="label text-xs">Deskripsi / Syarat & Ketentuan</label>
                    <input type="text" name="description" class="input text-xs"
                           placeholder="Penjelasan singkat promo untuk pelanggan..." value="{{ old('description') }}">
                </div>
            </div>
        </div>

        {{-- Bagian 2: Nilai & Batasan Potongan --}}
        <div>
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">Besaran Diskon & Perhitungan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="label text-xs">Tipe Nilai Potongan <span class="text-rose-500">*</span></label>
                    <select name="type" class="input text-xs" required>
                        <option value="percentage" @selected(old('type') === 'percentage')>Persentase (%)</option>
                        <option value="nominal" @selected(old('type') === 'nominal')>Nominal Tetap (Rp)</option>
                    </select>
                </div>

                <div>
                    <label class="label text-xs">Besaran Nilai Diskon <span class="text-rose-500">*</span></label>
                    <input type="number" name="value" class="input text-xs" required min="1"
                           placeholder="Misal 10 untuk 10%, atau 50000 untuk Rp 50.000" value="{{ old('value') }}">
                    @error('value') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label text-xs">Maksimal Potongan (Rp)</label>
                    <input type="number" name="max_discount" class="input text-xs" min="1"
                           placeholder="Opsional, batas maks jika persentase" value="{{ old('max_discount') }}">
                    <p class="text-[11px] text-slate-400 mt-1">Hanya berlaku untuk tipe persentase.</p>
                </div>
            </div>
        </div>

        {{-- Bagian 3: Syarat Minimal & Kuota --}}
        <div>
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">Syarat Transaksi & Kuota Penggunaan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="label text-xs">Minimal Subtotal Sewa (Rp)</label>
                    <input type="number" name="min_order_amount" class="input text-xs" min="0" step="10000"
                           placeholder="0 = Tanpa minimal" value="{{ old('min_order_amount', 0) }}">
                </div>

                <div>
                    <label class="label text-xs">Minimal Hari Sewa</label>
                    <input type="number" name="min_days" class="input text-xs" min="1"
                           placeholder="1" value="{{ old('min_days', 1) }}">
                </div>

                <div>
                    <label class="label text-xs">Kuota Pemakaian Global</label>
                    <input type="number" name="usage_limit" class="input text-xs" min="1"
                           placeholder="Kosongkan jika tak terbatas" value="{{ old('usage_limit') }}">
                </div>

                <div>
                    <label class="label text-xs">Kuota per Member/User</label>
                    <input type="number" name="usage_limit_per_user" class="input text-xs" min="1"
                           placeholder="1x / user" value="{{ old('usage_limit_per_user', 1) }}">
                </div>
            </div>
        </div>

        {{-- Bagian 4: Periode Berlaku --}}
        <div>
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">Periode Masa Berlaku</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label text-xs">Tanggal Mulai Berlaku</label>
                    <input type="datetime-local" name="starts_at" class="input text-xs" value="{{ old('starts_at') }}">
                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika langsung aktif saat dibuat.</p>
                </div>

                <div>
                    <label class="label text-xs">Tanggal Berakhir</label>
                    <input type="datetime-local" name="ends_at" class="input text-xs" value="{{ old('ends_at') }}">
                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika tidak ada batas waktu berakhir.</p>
                </div>
            </div>
        </div>

        {{-- Bagian 5: Cakupan Promo (Scope) --}}
        <div>
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">Cakupan Kendaraan & Lokasi (Opsional)</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="label text-xs">Batasi Kategori Mobil</label>
                    <select name="category_id" class="input text-xs">
                        <option value="">Semua Kategori Mobil</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label text-xs">Batasi Mobil Spesifik</label>
                    <select name="car_id" class="input text-xs">
                        <option value="">Semua Mobil</option>
                        @foreach ($cars as $car)
                            <option value="{{ $car->id }}" @selected(old('car_id') == $car->id)>{{ $car->brand }} {{ $car->name }} ({{ $car->plate_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label text-xs">Batasi Lokasi Pengambilan</label>
                    <select name="location_id" class="input text-xs">
                        <option value="">Semua Lokasi</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" @selected(old('location_id') == $loc->id)>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Bagian 6: Pengaturan Penggabungan & Status --}}
        <div class="border-t border-slate-100 pt-4 flex flex-wrap gap-6">
            <label class="flex items-center gap-2 text-xs font-semibold text-slate-800 cursor-pointer">
                <input type="checkbox" name="is_combinable" value="1" @checked(old('is_combinable', '1') == '1')
                       class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Boleh digabung dengan Diskon Tier Member
            </label>

            <label class="flex items-center gap-2 text-xs font-semibold text-slate-800 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') == '1')
                       class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Promo Aktif
            </label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline text-xs">Batal</a>
            <button type="submit" class="btn btn-primary text-xs">Simpan Promo Baru</button>
        </div>
    </form>
</div>
@endsection

