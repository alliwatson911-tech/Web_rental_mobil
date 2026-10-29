@extends('layouts.app')
@section('title', 'Ubah Profil Member - Padaidi Rent Car')

@section('content')
<div class="mx-auto max-w-2xl py-4">
    <div class="card card-accent-top p-6 md:p-8 bg-[#111827] border border-[#1e293b] shadow-xl">
        <div class="flex items-center justify-between pb-4 border-b border-[#1e293b]">
            <div>
                <span class="section-kicker mb-1">AKUN SAYA</span>
                <h1 class="text-xl font-black uppercase text-white tracking-wide">Ubah Profil Keanggotaan</h1>
                <p class="text-xs text-slate-400 mt-0.5">Perbarui informasi kontak dan data diri resmi Anda.</p>
            </div>
            <a href="{{ route('member.account') }}" class="btn btn-outline text-xs py-1.5 px-3 uppercase font-bold">← Kembali</a>
        </div>

        <form method="POST" action="{{ route('member.profile.update') }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="label text-slate-300" for="name">Nama Lengkap (sesuai KTP)</label>
                <input class="input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')<p class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="label text-slate-300" for="email">Alamat Email</label>
                    <input class="input" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')<p class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label text-slate-300" for="phone">Nomor WhatsApp / HP</label>
                    <input class="input" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                    @error('phone')<p class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="label text-slate-300" for="address">Alamat Domisili Lengkap</label>
                <textarea class="input" id="address" name="address" rows="2">{{ old('address', $profile->address) }}</textarea>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="label text-slate-300" for="nik">Nomor NIK KTP (16 Digit)</label>
                    <input class="input" id="nik" name="nik" value="{{ old('nik', $profile->nik) }}" maxlength="16" placeholder="7371xxxxxxxxxxxx">
                    @error('nik')<p class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label text-slate-300" for="sim_number">Nomor SIM A</label>
                    <input class="input" id="sim_number" name="sim_number" value="{{ old('sim_number', $profile->sim_number) }}" placeholder="Nomor SIM A Anda">
                </div>
            </div>

            <div class="mt-6 border-t border-[#1e293b] pt-5">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-300">Ganti Kata Sandi (Opsional)</h2>
                <p class="text-xs text-slate-400 mb-4 mt-0.5">Kosongkan bagian ini jika Anda tidak ingin mengubah kata sandi akun.</p>

                <div class="space-y-3">
                    <div>
                        <label class="label text-slate-300" for="current_password">Kata Sandi Saat Ini</label>
                        <input class="input" type="password" id="current_password" name="current_password" placeholder="••••••••">
                        @error('current_password')<p class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="label text-slate-300" for="password">Kata Sandi Baru</label>
                            <input class="input" type="password" id="password" name="password" placeholder="Min. 6 karakter">
                            @error('password')<p class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label text-slate-300" for="password_confirmation">Ulangi Sandi Baru</label>
                            <input class="input" type="password" id="password_confirmation" name="password_confirmation" placeholder="Konfirmasi sandi baru">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="btn btn-primary w-full py-3 font-black uppercase tracking-wider text-xs">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
