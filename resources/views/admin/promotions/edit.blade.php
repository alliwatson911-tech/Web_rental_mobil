@extends('layouts.admin')
@section('title', 'Ubah Promo: ' . $promotion->name)
@section('actions')
    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Daftar Promo</a>
@endsection

@section('content')
<div class="card p-6 max-w-4xl border border-[#1e293b] bg-[#111827]">
    <form method="POST" action="{{ route('admin.promotions.update', $promotion) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Bagian 1: Identitas Promo --}}
        <div>
            <h2 class="text-sm font-bold text-white border-b border-[#1e293b] pb-2 mb-4">Informasi Utama</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label text-xs text-slate-300">Nama Promo <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" class="input text-xs" required
                           value="{{ old('name', $promotion->name) }}">
                    @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label text-xs text-slate-300">Kode Voucher (Kosongkan bila otomatis)</label>
                    <input type="text" name="code" class="input text-xs font-mono uppercase text-[#F5A623]"
                           value="{{ old('code', $promotion->code) }}">
                    @error('code') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label text-xs text-slate-300">Tipe Target Promo <span class="text-rose-400">*</span></label>
                    <select name="target_type" class="input text-xs" required>
                        <option value="coupon" @selected(old('target_type', $promotion->target_type) === 'coupon')>Kode Voucher (Kupon input manual)</option>
                        <option value="first_order" @selected(old('target_type', $promotion->target_type) === 'first_order')>Pelanggan Baru (Sewa Pertama Kali)</option>
                        <option value="period" @selected(old('target_type', $promotion->target_type) === 'period')>Musiman / Periode Tertentu</option>
                        <option value="tier" @selected(old('target_type', $promotion->target_type) === 'tier')>Diskon Tingkat Member</option>
                    </select>
                </div>

                <div>
                    <label class="label text-xs text-slate-300">Deskripsi / Syarat & Ketentuan</label>
                    <input type="text" name="description" class="input text-xs"
                           value="{{ old('description', $promotion->description) }}">
                </div>
            </div>
        </div>

        {{-- Bagian 2: Nilai & Batasan Potongan --}}
        <div>
            <h2 class="text-sm font-bold text-white border-b border-[#1e293b] pb-2 mb-4">Besaran Diskon & Perhitungan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="label text-xs">Tipe Nilai Potongan <span class="text-rose-500">*</span></label>
                    <select name="type" class="input text-xs" required>
                        <option value="percentage" @selected(old('type', $promotion->type) === 'percentage')>Persentase (%)</option>
                        <option value="nominal" @selected(old('type', $promotion->type) === 'nominal')>Nominal Tetap (Rp)</option>
                    </select>
                </div>

                <div>
                    <label class="label text-xs">Besaran Nilai Diskon <span class="text-rose-500">*</span></label>
                    <input type="number" name="value" class="input text-xs" required min="1"
                           value="{{ old('value', $promotion->value) }}">
                    @error('value') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label text-xs">Maksimal Potongan (Rp)</label>
                    <input type="number" name="max_discount" class="input text-xs" min="1"
                           value="{{ old('max_discount', $promotion->max_discount) }}">
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
                           value="{{ old('min_order_amount', $promotion->min_order_amount) }}">
                </div>

                <div>
                    <label class="label text-xs">Minimal Hari Sewa</label>
                    <input type="number" name="min_days" class="input text-xs" min="1"
                           value="{{ old('min_days', $promotion->min_days) }}">
                </div>

                <div>
                    <label class="label text-xs">Kuota Pemakaian Global</label>
                    <input type="number" name="usage_limit" class="input text-xs" min="1"
                           value="{{ old('usage_limit', $promotion->usage_limit) }}">
                    <p class="text-[11px] text-slate-400 mt-1">Saat ini terpakai: {{ $promotion->used_count }}x</p>
                </div>

                <div>
                    <label class="label text-xs">Kuota per Member/User</label>
                    <input type="number" name="usage_limit_per_user" class="input text-xs" min="1"
                           value="{{ old('usage_limit_per_user', $promotion->usage_limit_per_user) }}">
                </div>
            </div>
        </div>

        {{-- Bagian 4: Periode Berlaku --}}
        <div>
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">Periode Masa Berlaku</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label text-xs">Tanggal Mulai Berlaku</label>
                    <input type="datetime-local" name="starts_at" class="input text-xs"
                           value="{{ old('starts_at', $promotion->starts_at?->format('Y-m-d\TH:i')) }}">
                </div>

                <div>
                    <label class="label text-xs">Tanggal Berakhir</label>
                    <input type="datetime-local" name="ends_at" class="input text-xs"
                           value="{{ old('ends_at', $promotion->ends_at?->format('Y-m-d\TH:i')) }}">
                </div>
            </div>
        </div>

        {{-- Bagian 5: Cakupan Promo (Scope) --}}
        <div>
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">Cakupan Kendaraan & Lokasi</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="label text-xs">Batasi Kategori Mobil</label>
                    <select name="category_id" class="input text-xs">
                        <option value="">Semua Kategori Mobil</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $promotion->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label text-xs">Batasi Mobil Spesifik</label>
                    <select name="car_id" class="input text-xs">
                        <option value="">Semua Mobil</option>
                        @foreach ($cars as $car)
                            <option value="{{ $car->id }}" @selected(old('car_id', $promotion->car_id) == $car->id)>{{ $car->brand }} {{ $car->name }} ({{ $car->plate_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label text-xs">Batasi Lokasi Pengambilan</label>
                    <select name="location_id" class="input text-xs">
                        <option value="">Semua Lokasi</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" @selected(old('location_id', $promotion->location_id) == $loc->id)>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Bagian 6: Pengaturan Penggabungan & Status --}}
        <div class="border-t border-slate-100 pt-4 flex flex-wrap gap-6">
            <label class="flex items-center gap-2 text-xs font-semibold text-slate-800 cursor-pointer">
                <input type="checkbox" name="is_combinable" value="1" @checked(old('is_combinable', $promotion->is_combinable))
                       class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Boleh digabung dengan Diskon Tier Member
            </label>

            <label class="flex items-center gap-2 text-xs font-semibold text-slate-800 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $promotion->is_active))
                       class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Promo Aktif
            </label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline text-xs">Batal</a>
            <button type="submit" class="btn btn-primary text-xs">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection

