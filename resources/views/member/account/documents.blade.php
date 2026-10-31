@extends('layouts.app')
@section('title', 'Dokumen e-KYC Member - Padaidi Rent Car')

@section('content')
<div class="mx-auto max-w-3xl py-4 space-y-6">
    <x-breadcrumb :back-url="route('member.account')" back-text="Kembali ke Dashboard Member" :crumbs="['Dashboard Member' => route('member.account'), 'Verifikasi Dokumen' => null]" />

    <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
        <div class="pb-4 border-b border-slate-200">
            <span class="section-kicker mb-1">VERIFIKASI IDENTITAS</span>
            <h1 class="text-xl font-black uppercase text-slate-900 tracking-wide">Kelola Dokumen e-KYC</h1>
            <p class="text-xs text-slate-600 mt-0.5">Unggah foto KTP dan SIM A asli untuk verifikasi sewa mobil lepas kunci instan.</p>
        </div>

        <div class="mt-4 rounded-lg bg-amber-50/70 border border-amber-200 p-4 text-xs text-slate-700 flex items-start gap-3">
            <x-icon name="lock" class="w-5 h-5 text-amber-700 shrink-0 mt-0.5" />
            <div>
                <p class="font-extrabold uppercase text-slate-900 tracking-wide">Keamanan Data &amp; Privasi Terjamin</p>
                <p class="mt-1 text-slate-600 leading-relaxed">
                    Dokumen Anda disimpan di direktori privat terenkripsi. Setiap akses dan tinjauan audit staf dicatat dalam log keamanan (<em>Document Access Audit Log</em>).
                </p>
            </div>
        </div>

        <div class="mt-6 grid gap-6 md:grid-cols-2">
            {{-- Foto e-KTP --}}
            <div class="rounded-lg bg-slate-50 border border-slate-200 p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h2 class="font-extrabold text-sm uppercase text-slate-900 tracking-wide">e-KTP (KTP Elektronik)</h2>
                        @if ($ktpDoc)
                            <span class="badge {{ $ktpDoc->status === 'verified' ? 'badge-success' : ($ktpDoc->status === 'rejected' ? 'badge-danger' : 'badge-warning') }} text-xs font-semibold">
                                {{ $ktpDoc->statusLabel() }}
                            </span>
                        @else
                            <span class="badge bg-slate-200 text-slate-700 border border-slate-300 text-xs">Belum Diunggah</span>
                        @endif
                    </div>

                    @if ($ktpDoc)
                        <div class="mt-3 text-xs text-slate-600 space-y-1.5">
                            <p>Diunggah: <span class="text-slate-900 font-medium">{{ tanggal($ktpDoc->created_at, 'd M Y H:i') }}</span></p>
                            <a href="{{ route('member.documents.view', $ktpDoc) }}" target="_blank" class="inline-block text-amber-700 hover:text-amber-800 font-bold underline mt-1">
                                Lihat Dokumen KTP Saya (Aman) ↗
                            </a>
                            @if ($ktpDoc->notes)
                                <p class="text-rose-700 text-xs mt-1.5 bg-rose-50 p-2 rounded border border-rose-200 font-medium">Catatan admin: {{ $ktpDoc->notes }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('member.documents.upload') }}" enctype="multipart/form-data" class="mt-5 border-t border-slate-200 pt-4 space-y-2.5">
                    @csrf
                    <input type="hidden" name="type" value="ktp">
                    <label class="label text-slate-700 text-xs font-bold">{{ $ktpDoc ? 'Unggah Ulang Foto KTP' : 'Unggah Foto KTP Asli' }}</label>
                    <input class="input text-xs bg-white text-slate-900 border-slate-300" type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    <button class="btn btn-primary w-full text-xs py-2 font-black uppercase tracking-wider">Simpan Dokumen KTP</button>
                </form>
            </div>

            {{-- Foto SIM A --}}
            <div class="rounded-lg bg-slate-50 border border-slate-200 p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h2 class="font-extrabold text-sm uppercase text-slate-900 tracking-wide">SIM A (Surat Izin Mengemudi)</h2>
                        @if ($simDoc)
                            <span class="badge {{ $simDoc->status === 'verified' ? 'badge-success' : ($simDoc->status === 'rejected' ? 'badge-danger' : 'badge-warning') }} text-xs font-semibold">
                                {{ $simDoc->statusLabel() }}
                            </span>
                        @else
                            <span class="badge bg-slate-200 text-slate-700 border border-slate-300 text-xs">Belum Diunggah</span>
                        @endif
                    </div>

                    @if ($simDoc)
                        <div class="mt-3 text-xs text-slate-600 space-y-1.5">
                            <p>Diunggah: <span class="text-slate-900 font-medium">{{ tanggal($simDoc->created_at, 'd M Y H:i') }}</span></p>
                            <a href="{{ route('member.documents.view', $simDoc) }}" target="_blank" class="inline-block text-amber-700 hover:text-amber-800 font-bold underline mt-1">
                                Lihat Dokumen SIM Saya (Aman) ↗
                            </a>
                            @if ($simDoc->notes)
                                <p class="text-rose-700 text-xs mt-1.5 bg-rose-50 p-2 rounded border border-rose-200 font-medium">Catatan admin: {{ $simDoc->notes }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('member.documents.upload') }}" enctype="multipart/form-data" class="mt-5 border-t border-slate-200 pt-4 space-y-2.5">
                    @csrf
                    <input type="hidden" name="type" value="sim">
                    <label class="label text-slate-700 text-xs font-bold">{{ $simDoc ? 'Unggah Ulang Foto SIM A' : 'Unggah Foto SIM A Asli' }}</label>
                    <input class="input text-xs bg-white text-slate-900 border-slate-300" type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    <button class="btn btn-primary w-full text-xs py-2 font-black uppercase tracking-wider">Simpan Dokumen SIM A</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
