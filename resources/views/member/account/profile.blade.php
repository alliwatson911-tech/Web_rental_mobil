@extends('layouts.app')
@section('title', 'Ubah Profil Member')

@section('content')
<div class="mx-auto max-w-2xl py-4">
    <div class="card p-6 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Ubah Profil Keanggotaan</h1>
                <p class="text-xs text-slate-500">Perbarui informasi kontak dan data diri Anda.</p>
            </div>
            <a href="{{ route('member.account') }}" class="btn btn-outline text-xs">← Kembali</a>
        </div>

        <form method="POST" action="{{ route('member.profile.update') }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="label" for="name">Nama Lengkap (sesuai KTP)</label>
                <input class="input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="label" for="email">Alamat Email</label>
                    <input class="input" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="phone">Nomor WhatsApp / HP</label>
                    <input class="input" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                    @error('phone')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="label" for="address">Alamat Domisili Lengkap</label>
                <textarea class="input" id="address" name="address" rows="2">{{ old('address', $profile->address) }}</textarea>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="label" for="nik">Nomor NIK KTP (16 Digit)</label>
                    <input class="input" id="nik" name="nik" value="{{ old('nik', $profile->nik) }}" maxlength="16" placeholder="7371xxxxxxxxxxxx">
                    @error('nik')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="sim_number">Nomor SIM A</label>
                    <input class="input" id="sim_number" name="sim_number" value="{{ old('sim_number', $profile->sim_number) }}" placeholder="Nomor SIM A Anda">
                </div>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-4">
                <h2 class="text-sm font-bold text-slate-800">Ganti Kata Sandi (Opsional)</h2>
                <p class="text-xs text-slate-500 mb-3">Kosongkan bagian ini jika Anda tidak ingin mengubah kata sandi.</p>

                <div class="space-y-3">
                    <div>
                        <label class="label" for="current_password">Kata Sandi Saat Ini</label>
                        <input class="input" type="password" id="current_password" name="current_password" placeholder="Masukkan kata sandi lama">
                        @error('current_password')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="label" for="password">Kata Sandi Baru</label>
                            <input class="input" type="password" id="password" name="password" placeholder="Min. 6 karakter">
                            @error('password')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                            <input class="input" type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi sandi baru">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button class="btn btn-primary w-full py-2.5 font-bold">Simpan Perubahan Profil</button>
            </div>
        </form>
    </div>
</div>
@endsection

