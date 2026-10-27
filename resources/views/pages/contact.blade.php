@extends('layouts.app')
@section('title', 'Kontak')

@section('content')
<h1 class="text-2xl font-bold text-slate-900">Kontak</h1>
<div class="mt-6 grid gap-4 md:grid-cols-2">
    <div class="card p-5 text-sm">
        <h2 class="font-bold text-slate-900">{{ $business['name'] }}</h2>
        <p class="mt-3"><span class="text-slate-500">Alamat</span><br>{{ $business['address'] }}</p>
        <p class="mt-3"><span class="text-slate-500">Jam layanan</span><br>{{ $business['hours'] }}</p>
        <p class="mt-3"><span class="text-slate-500">Email</span><br><a class="text-brand-600 underline" href="mailto:{{ $business['email'] }}">{{ $business['email'] }}</a></p>
    </div>
    <div class="card p-5">
        <h2 class="font-bold text-slate-900">WhatsApp admin</h2>
        <p class="mt-1 text-sm text-slate-500">Klik untuk langsung chat.</p>
        <div class="mt-3 space-y-2">
            @foreach ($business['admin_whatsapp'] as $i => $wa)
                <a class="btn btn-success w-full" target="_blank" rel="noopener" href="{{ \App\Support\WhatsApp::link($wa, 'Halo Padaidi, saya ingin bertanya tentang sewa mobil.') }}">Chat Admin {{ $i + 1 }} · {{ \App\Support\WhatsApp::display($wa) }}</a>
            @endforeach
        </div>
    </div>
    <div class="card overflow-hidden md:col-span-2">
        <iframe title="Peta lokasi" class="h-72 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q={{ urlencode($business['address']) }}&output=embed"></iframe>
    </div>
</div>
@endsection
