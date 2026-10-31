@extends('layouts.app')
@section('title', 'Ringkasan Pesanan - Padaidi Rent Car')

@section('content')
<x-breadcrumb :back-url="route('bookings.create', ['car' => $car] + array_intersect_key($data, array_flip(['pickup_location_id', 'pickup_date', 'return_date'])))" back-text="Kembali ke Form Pemesanan" :crumbs="['Daftar Armada' => route('cars.index'), $car->name => route('cars.show', $car), 'Ringkasan Pesanan' => null]" />

<ol class="mb-6 flex flex-wrap gap-2 text-xs font-bold uppercase tracking-wider">
    <li class="badge bg-slate-100 text-slate-600 border border-slate-200 px-3 py-1">1. Isi pesanan</li>
    <li class="badge badge-accent px-3 py-1">2. Ringkasan</li>
    <li class="badge bg-slate-100 text-slate-600 border border-slate-200 px-3 py-1">3. Selesai</li>
</ol>

<div class="grid gap-6 lg:grid-cols-3 items-start">
    {{-- Kolom Kiri: Detail Konfirmasi --}}
    <div class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm lg:col-span-2">
        <span class="section-kicker mb-1">KONFIRMASI AKHIR</span>
        <h1 class="text-xl md:text-2xl font-black uppercase text-slate-900 tracking-wide mb-4">Periksa pesanan Anda</h1>
        
        <dl class="grid gap-4 text-xs md:grid-cols-2 pt-4 border-t border-slate-200">
            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                <dt class="text-[10px] uppercase font-bold text-slate-500">Mobil</dt>
                <dd class="text-sm font-bold text-slate-900 mt-1">{{ $car->name }} ({{ $car->category->name }})</dd>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                <dt class="text-[10px] uppercase font-bold text-slate-500">Lokasi ambil</dt>
                <dd class="text-sm font-bold text-slate-900 mt-1">{{ $location->name }}</dd>
            </div>
            @if ($location->requires_address)
                <div class="md:col-span-2 bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                    <dt class="text-[10px] uppercase font-bold text-slate-500">Alamat antar</dt>
                    <dd class="text-sm font-bold text-amber-700 mt-1">{{ $data['delivery_address'] }}</dd>
                </div>
            @endif
            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                <dt class="text-[10px] uppercase font-bold text-slate-500">Tanggal ambil</dt>
                <dd class="text-sm font-bold text-slate-900 mt-1">{{ tanggal($data['pickup_date'], 'l, d F Y') }}</dd>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                <dt class="text-[10px] uppercase font-bold text-slate-500">Tanggal kembali</dt>
                <dd class="text-sm font-bold text-slate-900 mt-1">{{ tanggal($data['return_date'], 'l, d F Y') }}</dd>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                <dt class="text-[10px] uppercase font-bold text-slate-500">Nama</dt>
                <dd class="text-sm font-bold text-slate-900 mt-1">{{ $data['customer_name'] }}</dd>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                <dt class="text-[10px] uppercase font-bold text-slate-500">HP/WhatsApp</dt>
                <dd class="text-sm font-bold text-slate-900 mt-1">{{ $data['customer_phone'] }}</dd>
            </div>
            @if (!empty($data['customer_email']))
                <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                    <dt class="text-[10px] uppercase font-bold text-slate-500">Email</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1">{{ $data['customer_email'] }}</dd>
                </div>
            @endif
            @if (!empty($data['notes']))
                <div class="md:col-span-2 bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                    <dt class="text-[10px] uppercase font-bold text-slate-500">Catatan</dt>
                    <dd class="text-xs text-slate-700 mt-1 leading-relaxed">{{ $data['notes'] }}</dd>
                </div>
            @endif
        </dl>
    </div>

    {{-- Kolom Kanan: Rincian Biaya --}}
    <aside class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
        <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide mb-4">Rincian biaya</h2>
        
        <table class="w-full text-xs">
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="py-2.5 text-slate-600">Sewa {{ rupiah($quote['daily_price']) }} × {{ $quote['days'] }} hari</td>
                    <td class="py-2.5 text-right font-bold text-slate-900">{{ rupiah($quote['base_total']) }}</td>
                </tr>
                @foreach ($quote['addons'] as $line)
                    <tr>
                        <td class="py-2.5 text-slate-600">
                            {{ $line['name'] }}
                            @if($line['charge_type'] === 'per_day')
                                <span class="text-slate-500">({{ rupiah($line['unit_price']) }} × {{ $line['quantity'] }} hari)</span>
                            @endif
                        </td>
                        <td class="py-2.5 text-right font-bold text-slate-900">{{ rupiah($line['subtotal']) }}</td>
                    </tr>
                @endforeach

                {{-- Rincian Diskon Berjenjang --}}
                @if (! empty($quote['tier_discount']) && $quote['tier_discount'] > 0)
                    <tr>
                        <td class="py-2.5 text-amber-800 font-medium">
                            Diskon Member {{ $quote['discount_snapshot']['tier']['name'] ?? '' }} ({{ $quote['discount_snapshot']['tier']['percentage'] ?? '' }}%)
                        </td>
                        <td class="py-2.5 text-right text-amber-800 font-black">
                            −{{ rupiah($quote['tier_discount']) }}
                        </td>
                    </tr>
                @endif

                @if (! empty($quote['promo_discount']) && $quote['promo_discount'] > 0)
                    <tr>
                        <td class="py-2.5 text-emerald-700 font-medium">
                            Kode Promo ({{ $quote['promo_code'] }})
                        </td>
                        <td class="py-2.5 text-right text-emerald-700 font-black">
                            −{{ rupiah($quote['promo_discount']) }}
                        </td>
                    </tr>
                @elseif (! empty($quote['discount']) && $quote['discount'] > 0 && empty($quote['tier_discount']) && empty($quote['promo_discount']))
                    <tr>
                        <td class="py-2.5 text-emerald-700 font-medium">Diskon</td>
                        <td class="py-2.5 text-right text-emerald-700 font-black">−{{ rupiah($quote['discount']) }}</td>
                    </tr>
                @endif

                <tr class="border-t-2 border-slate-200 text-sm font-black text-slate-900">
                    <td class="pt-3 uppercase">Total Tagihan</td>
                    <td class="pt-3 text-right text-amber-700 text-base">{{ rupiah($quote['total']) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Opsi Pembayaran DP 30% & Pelunasan --}}
        <div class="mt-4 rounded-lg bg-amber-50/60 p-3.5 text-xs border border-amber-200 space-y-1.5">
            <div class="flex justify-between text-slate-700">
                <span>Opsi Uang Muka (DP 30%):</span>
                <strong class="text-amber-800 font-mono font-black">{{ rupiah($quote['dp_amount']) }}</strong>
            </div>
            <div class="flex justify-between text-slate-600 text-[11px]">
                <span>Sisa Pelunasan (70%):</span>
                <span>{{ rupiah($quote['settlement_amount']) }}</span>
            </div>
        </div>

        <p class="mt-3 text-[11px] text-slate-500 leading-relaxed italic">
            *Belum termasuk deposit {{ rupiah($business['terms']['deposit']) }} (dibayarkan saat serah terima kendaraan di pool dan dikembalikan penuh).
        </p>

        <form method="POST" action="{{ route('bookings.store', $car) }}" class="mt-6">
            @csrf
            @foreach (['pickup_location_id', 'pickup_date', 'return_date', 'delivery_address', 'customer_name', 'customer_phone', 'customer_email', 'customer_address', 'notes', 'agree_terms', 'promo_code'] as $field)
                @if (isset($data[$field]))<input type="hidden" name="{{ $field }}" value="{{ $data[$field] }}">@endif
            @endforeach
            @foreach ($data['addons'] ?? [] as $addonId)
                <input type="hidden" name="addons[]" value="{{ $addonId }}">
            @endforeach
            <button class="btn btn-primary w-full py-3.5 font-black uppercase tracking-wider text-xs shadow-md">
                Kirim pesanan
            </button>
        </form>

        <form method="GET" action="{{ route('bookings.create', $car) }}" class="mt-2.5">
            <input type="hidden" name="pickup_location_id" value="{{ $data['pickup_location_id'] }}">
            <input type="hidden" name="pickup_date" value="{{ $data['pickup_date'] }}">
            <input type="hidden" name="return_date" value="{{ $data['return_date'] }}">
            @if (! empty($data['promo_code']))<input type="hidden" name="promo_code" value="{{ $data['promo_code'] }}">@endif
            <button class="btn btn-outline w-full py-2 text-xs uppercase font-bold tracking-wider">
                Ubah pesanan
            </button>
        </form>
    </aside>
</div>
@endsection