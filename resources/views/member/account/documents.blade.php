@extends('layouts.app')
@section('title', 'Dokumen e-KYC Member - Padaidi Rent Car')

@section('content')
<div class="mx-auto max-w-3xl py-4 space-y-6">
    <x-breadcrumb :back-url="route('member.account')" back-text="Kembali ke Dashboard Member" :crumbs="['Dashboard Member' => route('member.account'), 'Verifikasi Dokumen' => null]" />

    <div class="card card-accent-top p-6 md:p-8 bg-[#111827] border border-[#1e293b] shadow-xl">
        <div class="flex items-center justify-between pb-4 border-b border-[#1e293b]">
            <div>
                <span class="section-kicker mb-1">VERIFIKASI IDENTITAS</span>
                <h1 class="text-xl font-black uppercase text-white tracking-wide">Kelola Dokumen e-KYC</h1>
                <p class="text-xs text-slate-400 mt-0.5">Unggah foto KTP dan SIM A asli untuk verifikasi sewa mobil lepas kunci instan.</p>
            </div>
            <a href="{{ route('member.account') }}" class="btn btn-outline text-xs py-1.5 px-3 uppercase font-bold">← Kembali</a>
        </div>

        <div class="mt-4 rounded bg-[#0b1220] border border-[#1e293b] p-4 text-xs text-slate-300 flex items-start gap-3">
            <x-icon name="lock" class="w-5 h-5 text-[#F5A623] shrink-0 mt-0.5" />
            <div>
                <p class="font-extrabold uppercase text-white tracking-wide">Keamanan Data &amp; Privasi Terjamin</p>
                <p class="mt-1 text-slate-400 leading-relaxed">
                    Dokumen Anda disimpan di direktori privat terenkripsi. Setiap akses dan tinjauan audit staf dicatat dalam log keamanan (*Document Access Audit Log*).
                </p>
            </div>
        </div>

        <div class="mt-6 grid gap-6 md:grid-cols-2">
            {{-- Foto e-KTP --}}
            <div class="rounded bg-[#0b1220] border border-[#1e293b] p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h2 class="font-extrabold text-sm uppercase text-white tracking-wide">e-KTP (KTP Elektronik)</h2>
                        @if ($ktpDoc)
                            <span class="badge {{ $ktpDoc->status === 'verified' ? 'badge-success' : ($ktpDoc->status === 'rejected' ? 'badge-danger' : 'badge-warning') }} text-xs">
                                {{ $ktpDoc->statusLabel() }}
                            </span>
                        @else
                            <span class="badge bg-slate-800 text-slate-400 border border-slate-700 text-xs">Belum Diunggah</span>
                        @endif
                    </div>

                    @if ($ktpDoc)
                        <div class="mt-3 text-xs text-slate-400 space-y-1.5">
                            <p>Diunggah: <span class="text-slate-300">{{ tanggal($ktpDoc->created_at, 'd M Y H:i') }}</span></p>
                            <a href="{{ route('member.documents.view', $ktpDoc) }}" target="_blank" class="inline-block text-amber-400 hover:text-amber-300 font-bold underline mt-1">
                                Lihat Dokumen KTP Saya (Aman) ↗
                            </a>
                            @if ($ktpDoc->notes)
                                <p class="text-rose-400 italic mt-1.5 bg-rose-950/40 p-2 rounded border border-rose-800/40">Catatan admin: {{ $ktpDoc->notes }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('member.documents.upload') }}" enctype="multipart/form-data" class="mt-5 border-t border-[#1e293b] pt-4 space-y-2.5">
                    @csrf
                    <input type="hidden" name="type" value="ktp">
                    <label class="label text-slate-300 text-xs font-bold">{{ $ktpDoc ? 'Unggah Ulang Foto KTP' : 'Unggah Foto KTP Asli' }}</label>
                    <input class="input text-xs" type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    <button class="btn btn-primary w-full text-xs py-2 font-black uppercase tracking-wider">Simpan Dokumen KTP</button>
                </form>
            </div>

            {{-- Foto SIM A --}}
            <div class="rounded bg-[#0b1220] border border-[#1e293b] p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h2 class="font-extrabold text-sm uppercase text-white tracking-wide">SIM A (Surat Izin Mengemudi)</h2>
                        @if ($simDoc)
                            <span class="badge {{ $simDoc->status === 'verified' ? 'badge-success' : ($simDoc->status === 'rejected' ? 'badge-danger' : 'badge-warning') }} text-xs">
                                {{ $simDoc->statusLabel() }}
                            </span>
                        @else
                            <span class="badge bg-slate-800 text-slate-400 border border-slate-700 text-xs">Belum Diunggah</span>
                        @endif
                    </div>

                    @if ($simDoc)
                        <div class="mt-3 text-xs text-slate-400 space-y-1.5">
                            <p>Diunggah: <span class="text-slate-300">{{ tanggal($simDoc->created_at, 'd M Y H:i') }}</span></p>
                            <a href="{{ route('member.documents.view', $simDoc) }}" target="_blank" class="inline-block text-amber-400 hover:text-amber-300 font-bold underline mt-1">
                                Lihat Dokumen SIM Saya (Aman) ↗
                            </a>
                            @if ($simDoc->notes)
                                <p class="text-rose-400 italic mt-1.5 bg-rose-950/40 p-2 rounded border border-rose-800/40">Catatan admin: {{ $simDoc->notes }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('member.documents.upload') }}" enctype="multipart/form-data" class="mt-5 border-t border-[#1e293b] pt-4 space-y-2.5">
                    @csrf
                    <input type="hidden" name="type" value="sim">
                    <label class="label text-slate-300 text-xs font-bold">{{ $simDoc ? 'Unggah Ulang Foto SIM A' : 'Unggah Foto SIM A Asli' }}</label>
                    <input class="input text-xs" type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    <button class="btn btn-primary w-full text-xs py-2 font-black uppercase tracking-wider">Simpan Dokumen SIM A</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
