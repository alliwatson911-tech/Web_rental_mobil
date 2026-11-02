@extends('layouts.app')
@section('title', '500 - Kesalahan Server · Padaidi Rent Car')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center card p-8 bg-white border border-slate-200 shadow-sm rounded-xl">
        <div class="mx-auto w-20 h-20 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center text-3xl font-black mb-6">
            <x-icon name="tool" class="w-10 h-10 text-rose-500" />
        </div>
        <span class="inline-block px-3 py-1 bg-rose-100 text-rose-800 rounded-full text-xs font-bold uppercase tracking-wider mb-3">
            Error 500 · Masalah Sistem
        </span>
        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-2">
            Terjadi Kesalahan Sistem
        </h1>
        <p class="text-sm text-slate-600 mb-6 leading-relaxed">
            Sistem kami sedang mengalami kendala teknis sementara. Tim kami telah mencatat peristiwa ini. Untuk pemesanan mendesak, silakan langsung menghubungi WhatsApp admin kami.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-primary w-full sm:w-auto px-6 py-2.5 font-bold uppercase text-xs tracking-wider">
                Kembali ke Beranda
            </a>
            <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('padaidi.admin1_phone', '6282197233174')) }}?text={{ urlencode('Halo Admin Padaidi Rent Car, saya mengalami kendala teknis saat memesan mobil.') }}"
               target="_blank" rel="noopener noreferrer"
               class="btn btn-outline w-full sm:w-auto px-6 py-2.5 font-bold uppercase text-xs tracking-wider text-emerald-700 border-emerald-300 hover:bg-emerald-50">
                Bantuan WhatsApp
            </a>
        </div>
    </div>
</div>
@endsection

