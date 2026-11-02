@extends('layouts.app')
@section('title', '404 - Halaman Tidak Ditemukan · Padaidi Rent Car')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center card p-8 bg-white border border-slate-200 shadow-sm rounded-xl">
        <div class="mx-auto w-20 h-20 rounded-2xl bg-amber-50 border border-amber-200 text-[#F5A623] flex items-center justify-center text-3xl font-black mb-6">
            <x-icon name="car" class="w-10 h-10 text-[#F5A623]" />
        </div>
        <span class="inline-block px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-xs font-bold uppercase tracking-wider mb-3">
            Error 404 · Halaman Hilang
        </span>
        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-2">
            Halaman Tidak Ditemukan
        </h1>
        <p class="text-sm text-slate-600 mb-6 leading-relaxed">
            Halaman, unit mobil, atau kode pesanan yang Anda tuju mungkin telah dipindahkan, dihapus, atau tautan yang dimasukkan kurang tepat.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-primary w-full sm:w-auto px-6 py-2.5 font-bold uppercase text-xs tracking-wider">
                Kembali ke Beranda
            </a>
            <a href="{{ route('cars.index') }}" class="btn btn-outline w-full sm:w-auto px-6 py-2.5 font-bold uppercase text-xs tracking-wider">
                Lihat Armada Mobil
            </a>
        </div>
    </div>
</div>
@endsection

