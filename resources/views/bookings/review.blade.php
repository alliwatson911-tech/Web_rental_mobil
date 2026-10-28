@extends('layouts.app')
@section('title', 'Ringkasan Pesanan')

@section('content')
<ol class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">
    <li class="badge bg-slate-200 text-slate-600">1. Isi pesanan</li>
    <li class="badge bg-brand-600 text-white">2. Ringkasan</li>
    <li class="badge bg-slate-200 text-slate-600">3. Selesai</li>
</ol>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="card p-5 lg:col-span-2">
        <h1 class="text-xl font-bold text-slate-900">Periksa pesanan Anda</h1>
        <dl class="mt-4 grid gap-3 text-sm md:grid-cols-2">
            <div><dt class="text-slate-500">Mobil</dt><dd class="font-semibold">{{ $car->name }} ({{ $car->category->name }})</dd></div>
            <div><dt class="text-slate-500">Lokasi ambil</dt><dd class="font-semibold">{{ $location->name }}</dd></div>
            @if ($location->requires_address)
                <div class="md:col-span-2"><dt class="text-slate-500">Alamat antar</dt><dd class="font-semibold">{{ $data['delivery_address'] }}</dd></div>
            @endif
            <div><dt class="text-slate-500">Tanggal ambil</dt><dd class="font-semibold">{{ tanggal($data['pickup_date'], 'l, d F Y') }}</dd></div>
            <div><dt class="text-slate-500">Tanggal kembali</dt><dd class="font-semibold">{{ tanggal($data['return_date'], 'l, d F Y') }}</dd></div>
            <div><dt class="text-slate-500">Nama</dt><dd class="font-semibold">{{ $data['customer_name'] }}</dd></div>
            <div><dt class="text-slate-500">HP/WhatsApp</dt><dd class="font-semibold">{{ $data['customer_phone'] }}</dd></div>
            @if (!empty($data['customer_email']))<div><dt class="text-slate-500">Email</dt><dd class="font-semibold">{{ $data['customer_email'] }}</dd></div>@endif
            @if (!empty($data['notes']))<div class="md:col-span-2"><dt class="text-slate-500">Catatan</dt><dd>{{ $data['notes'] }}</dd></div>@endif
        </dl>
    </div>

    <aside class="card p-5">
        <h2 class="font-bold text-slate-900">Rincian biaya</h2>
        <table class="mt-3 w-full text-sm">
            <tr><td class="py-1">Sewa {{ rupiah($quote['daily_price']) }} × {{ $quote['days'] }} hari</td><td class="py-1 text-right">{{ rupiah($quote['base_total']) }}</td></tr>
            @foreach ($quote['addons'] as $line)
                <tr><td class="py-1">{{ $line['name'] }} @if($line['charge_type'] === 'per_day')({{ rupiah($line['unit_price']) }} × {{ $line['quantity'] }} hari)@endif</td><td class="py-1 text-right">{{ rupiah($line['subtotal']) }}</td></tr>
            @endforeach

            {{-- Rincian Diskon Berjenjang (Fase 2) --}}
            @if (! empty($quote['tier_discount']) && $quote['tier_discount'] > 0)
                <tr>
                    <td class="py-1 text-amber-800">
                        Diskon Member {{ $quote['discount_snapshot']['tier']['name'] ?? '' }} ({{ $quote['discount_snapshot']['tier']['percentage'] ?? '' }}%)
                    </td>
                    <td class="py-1 text-right text-amber-800 font-semibold">
                        −{{ rupiah($quote['tier_discount']) }}
                    </td>
                </tr>
            @endif

            @if (! empty($quote['promo_discount']) && $quote['promo_discount'] > 0)
                <tr>
                    <td class="py-1 text-emerald-800">
                        Kode Promo ({{ $quote['promo_code'] }})
                    </td>
                    <td class="py-1 text-right text-emerald-800 font-semibold">
                        −{{ rupiah($quote['promo_discount']) }}
                    </td>
                </tr>
            @elseif (! empty($quote['discount']) && $quote['discount'] > 0 && empty($quote['tier_discount']) && empty($quote['promo_discount']))
                <tr><td class="py-1">Diskon</td><td class="py-1 text-right">−{{ rupiah($quote['discount']) }}</td></tr>
            @endif

            <tr class="border-t border-slate-200 text-base font-extrabold text-brand-700"><td class="pt-2">Total</td><td class="pt-2 text-right">{{ rupiah($quote['total']) }}</td></tr>
        </table>

        {{-- Opsi Pembayaran DP 30% & Pelunasan --}}
        <div class="mt-3 rounded-lg bg-brand-50/60 p-3 text-xs border border-brand-100 space-y-1">
            <div class="flex justify-between text-slate-700">
                <span>Opsi Uang Muka (DP 30%):</span>
                <strong class="text-brand-900">{{ rupiah($quote['dp_amount']) }}</strong>
            </div>
            <div class="flex justify-between text-slate-500 text-[11px]">
                <span>Sisa Pelunasan (70%):</span>
                <span>{{ rupiah($quote['settlement_amount']) }}</span>
            </div>
        </div>

        <p class="mt-2 text-xs text-slate-500">Belum termasuk deposit {{ rupiah($business['terms']['deposit']) }} (dibayar saat ambil mobil, dikembalikan).</p>

        <form method="POST" action="{{ route('bookings.store', $car) }}" class="mt-4">
            @csrf
            @foreach (['pickup_location_id', 'pickup_date', 'return_date', 'delivery_address', 'customer_name', 'customer_phone', 'customer_email', 'customer_address', 'notes', 'agree_terms', 'promo_code'] as $field)
                @if (isset($data[$field]))<input type="hidden" name="{{ $field }}" value="{{ $data[$field] }}">@endif
            @endforeach
            @foreach ($data['addons'] ?? [] as $addonId)
                <input type="hidden" name="addons[]" value="{{ $addonId }}">
            @endforeach
            <button class="btn btn-primary w-full py-3">Kirim pesanan</button>
        </form>
        <form method="GET" action="{{ route('bookings.create', $car) }}" class="mt-2">
            <input type="hidden" name="pickup_location_id" value="{{ $data['pickup_location_id'] }}">
            <input type="hidden" name="pickup_date" value="{{ $data['pickup_date'] }}">
            <input type="hidden" name="return_date" value="{{ $data['return_date'] }}">
            @if (! empty($data['promo_code']))<input type="hidden" name="promo_code" value="{{ $data['promo_code'] }}">@endif
            <button class="btn btn-outline w-full">Ubah pesanan</button>
        </form>
    </aside>
</div>
@endsection
