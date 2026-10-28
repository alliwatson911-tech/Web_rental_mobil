@extends('layouts.app')
@section('title', 'Dokumen e-KYC Member')

@section('content')
<div class="mx-auto max-w-3xl py-4 space-y-6">
    <div class="card p-6 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Kelola Dokumen e-KYC</h1>
                <p class="text-xs text-slate-500">Unggah foto KTP dan SIM A Anda untuk verifikasi identitas resmi sewa mobil lepas kunci.</p>
            </div>
            <a href="{{ route('member.account') }}" class="btn btn-outline text-xs">← Kembali ke Akun</a>
        </div>

        <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200 p-4 text-xs text-slate-600 flex items-start gap-3">
            <span class="text-xl">🔒</span>
            <div>
                <p class="font-bold text-slate-800">Keamanan Data & Privasi Terjamin</p>
                <p class="mt-0.5">Dokumen Anda disimpan di direktori terisolasi dan privat (*private storage*). Setiap akses dan tinjauan dokumen diaudit serta dicatat dalam log keamanan (*Document Access Log*).</p>
            </div>
        </div>

        <div class="mt-6 grid gap-6 md:grid-cols-2">
            {{-- Foto e-KTP --}}
            <div class="rounded-xl border border-slate-200 p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-slate-900">e-KTP (Kartu Tanda Penduduk)</h2>
                        @if ($ktpDoc)
                            <span class="badge {{ $ktpDoc->status === 'verified' ? 'bg-emerald-100 text-emerald-800' : ($ktpDoc->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }} text-xs">
                                {{ $ktpDoc->statusLabel() }}
                            </span>
                        @else
                            <span class="badge bg-slate-100 text-slate-600 text-xs">Belum Diunggah</span>
                        @endif
                    </div>

                    @if ($ktpDoc)
                        <div class="mt-3 text-xs text-slate-600 space-y-1">
                            <p>Diunggah: {{ tanggal($ktpDoc->created_at, 'd M Y H:i') }}</p>
                            <a href="{{ route('member.documents.view', $ktpDoc) }}" target="_blank" class="inline-block text-brand-600 font-semibold underline mt-1">Lihat Dokumen KTP Saya (Aman)</a>
                            @if ($ktpDoc->notes)
                                <p class="text-rose-600 italic mt-1">Catatan admin: {{ $ktpDoc->notes }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('member.documents.upload') }}" enctype="multipart/form-data" class="mt-4 border-t border-slate-100 pt-3 space-y-2">
                    @csrf
                    <input type="hidden" name="type" value="ktp">
                    <label class="label text-xs font-semibold">{{ $ktpDoc ? 'Unggah Ulang Foto KTP' : 'Unggah Foto KTP Asli' }}</label>
                    <input class="input text-xs" type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    <button class="btn btn-outline w-full text-xs py-1.5 font-semibold">Simpan Dokumen KTP</button>
                </form>
            </div>

            {{-- Foto SIM A --}}
            <div class="rounded-xl border border-slate-200 p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-slate-900">SIM A (Surat Izin Mengemudi)</h2>
                        @if ($simDoc)
                            <span class="badge {{ $simDoc->status === 'verified' ? 'bg-emerald-100 text-emerald-800' : ($simDoc->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }} text-xs">
                                {{ $simDoc->statusLabel() }}
                            </span>
                        @else
                            <span class="badge bg-slate-100 text-slate-600 text-xs">Belum Diunggah</span>
                        @endif
                    </div>

                    @if ($simDoc)
                        <div class="mt-3 text-xs text-slate-600 space-y-1">
                            <p>Diunggah: {{ tanggal($simDoc->created_at, 'd M Y H:i') }}</p>
                            <a href="{{ route('member.documents.view', $simDoc) }}" target="_blank" class="inline-block text-brand-600 font-semibold underline mt-1">Lihat Dokumen SIM Saya (Aman)</a>
                            @if ($simDoc->notes)
                                <p class="text-rose-600 italic mt-1">Catatan admin: {{ $simDoc->notes }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('member.documents.upload') }}" enctype="multipart/form-data" class="mt-4 border-t border-slate-100 pt-3 space-y-2">
                    @csrf
                    <input type="hidden" name="type" value="sim">
                    <label class="label text-xs font-semibold">{{ $simDoc ? 'Unggah Ulang Foto SIM A' : 'Unggah Foto SIM A Asli' }}</label>
                    <input class="input text-xs" type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    <button class="btn btn-outline w-full text-xs py-1.5 font-semibold">Simpan Dokumen SIM A</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

