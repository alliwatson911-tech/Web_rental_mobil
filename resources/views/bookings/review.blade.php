@extends('layouts.app')
@section('title', 'Ringkasan Pesanan - Padaidi Rent Car')

@section('content')
<x-breadcrumb :back-url="route('bookings.create', ['car' => $car] + array_intersect_key($data, array_flip(['pickup_location_id', 'pickup_date', 'return_date'])))" back-text="Kembali ke Form Pemesanan" :crumbs="['Daftar Armada' => route('cars.index'), $car->name => route('cars.show', $car), 'Ringkasan Pesanan' => null]" />

<ol class="mb-6 flex flex-wrap gap-2 text-xs font-bold uppercase tracking-wider">
    <li class="badge bg-slate-800 text-slate-400 border border-slate-700 px-3 py-1">1. Isi pesanan</li>
    <li class="badge badge-accent px-3 py-1">2. Ringkasan</li>
    <li class="badge bg-slate-800 text-slate-400 border border-slate-700 px-3 py-1">3. Selesai</li>
</ol>

<div class="grid gap-6 lg:grid-cols-3 items-start">
    {{-- Kolom Kiri: Detail Konfirmasi --}}
    <div class="card card-accent-top p-6 md:p-8 bg-[#111827] border border-[#1e293b] lg:col-span-2">
        <span class="section-kicker mb-1">KONFIRMASI AKHIR</span>
        <h1 class="text-xl md:text-2xl font-black uppercase text-white tracking-wide mb-4">Periksa pesanan Anda</h1>
        
        <dl class="grid gap-4 text-xs md:grid-cols-2 pt-4 border-t border-[#1e293b]">
            <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                <dt class="text-[10px] uppercase font-bold text-slate-400">Mobil</dt>
                <dd class="text-sm font-bold text-white mt-1">{{ $car->name }} ({{ $car->category->name }})</dd>
            </div>
            <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                <dt class="text-[10px] uppercase font-bold text-slate-400">Lokasi ambil</dt>
                <dd class="text-sm font-bold text-white mt-1">{{ $location->name }}</dd>
            </div>
            @if ($location->requires_address)
                <div class="md:col-span-2 bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                    <dt class="text-[10px] uppercase font-bold text-slate-400">Alamat antar</dt>
                    <dd class="text-sm font-bold text-amber-300 mt-1">{{ $data['delivery_address'] }}</dd>
                </div>
            @endif
            <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                <dt class="text-[10px] uppercase font-bold text-slate-400">Tanggal ambil</dt>
                <dd class="text-sm font-bold text-white mt-1">{{ tanggal($data['pickup_date'], 'l, d F Y') }}</dd>
            </div>
            <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                <dt class="text-[10px] uppercase font-bold text-slate-400">Tanggal kembali</dt>
                <dd class="text-sm font-bold text-white mt-1">{{ tanggal($data['return_date'], 'l, d F Y') }}</dd>
            </div>
            <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                <dt class="text-[10px] uppercase font-bold text-slate-400">Nama</dt>
                <dd class="text-sm font-bold text-white mt-1">{{ $data['customer_name'] }}</dd>
            </div>
            <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                <dt class="text-[10px] uppercase font-bold text-slate-400">HP/WhatsApp</dt>
                <dd class="text-sm font-bold text-white mt-1">{{ $data['customer_phone'] }}</dd>
            </div>
            @if (!empty($data['customer_email']))
                <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                    <dt class="text-[10px] uppercase font-bold text-slate-400">Email</dt>
                    <dd class="text-sm font-bold text-white mt-1">{{ $data['customer_email'] }}</dd>
                </div>
            @endif
            @if (!empty($data['notes']))
                <div class="md:col-span-2 bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                    <dt class="text-[10px] uppercase font-bold text-slate-400">Catatan</dt>
                    <dd class="text-xs text-slate-300 mt-1 leading-relaxed">{{ $data['notes'] }}</dd>
                </div>
            @endif
        </dl>
    </div>

    {{-- Kolom Kanan: Rincian Biaya --}}
    <aside class="card card-accent-top p-6 md:p-8 bg-[#111827] border border-[#1e293b] shadow-2xl">
        <h2 class="font-extrabold text-base uppercase text-white tracking-wide mb-4">Rincian biaya</h2>
        
        <table class="w-full text-xs">
            <tbody class="divide-y divide-[#1e293b]">
                <tr>
                    <td class="py-2.5 text-slate-300">Sewa {{ rupiah($quote['daily_price']) }} × {{ $quote['days'] }} hari</td>
                    <td class="py-2.5 text-right font-bold text-white">{{ rupiah($quote['base_total']) }}</td>
                </tr>
                @foreach ($quote['addons'] as $line)
                    <tr>
                        <td class="py-2.5 text-slate-300">
                            {{ $line['name'] }}
                            @if($line['charge_type'] === 'per_day')
                                <span class="text-slate-500">({{ rupiah($line['unit_price']) }} × {{ $line['quantity'] }} hari)</span>
                            @endif
                        </td>
                        <td class="py-2.5 text-right font-bold text-white">{{ rupiah($line['subtotal']) }}</td>
                    </tr>
                @endforeach

                {{-- Rincian Diskon Berjenjang --}}
                @if (! empty($quote['tier_discount']) && $quote['tier_discount'] > 0)
                    <tr>
                        <td class="py-2.5 text-amber-400 font-medium">
                            Diskon Member {{ $quote['discount_snapshot']['tier']['name'] ?? '' }} ({{ $quote['discount_snapshot']['tier']['percentage'] ?? '' }}%)
                        </td>
                        <td class="py-2.5 text-right text-amber-400 font-extrabold">
                            −{{ rupiah($quote['tier_discount']) }}
                        </td>
                    </tr>
                @endif

                @if (! empty($quote['promo_discount']) && $quote['promo_discount'] > 0)
                    <tr>
                        <td class="py-2.5 text-emerald-400 font-medium">
                            Kode Promo ({{ $quote['promo_code'] }})
                        </td>
                        <td class="py-2.5 text-right text-emerald-400 font-extrabold">
                            −{{ rupiah($quote['promo_discount']) }}
                        </td>
                    </tr>
                @elseif (! empty($quote['discount']) && $quote['discount'] > 0 && empty($quote['tier_discount']) && empty($quote['promo_discount']))
                    <tr>
                        <td class="py-2.5 text-emerald-400 font-medium">Diskon</td>
                        <td class="py-2.5 text-right text-emerald-400 font-extrabold">−{{ rupiah($quote['discount']) }}</td>
                    </tr>
                @endif

                <tr class="border-t-2 border-[#1e293b] text-sm font-black text-white">
                    <td class="pt-3 uppercase">Total Tagihan</td>
                    <td class="pt-3 text-right text-[#F5A623] text-base">{{ rupiah($quote['total']) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Opsi Pembayaran DP 30% & Pelunasan --}}
        <div class="mt-4 rounded bg-[#0b1220] p-3.5 text-xs border border-[#1e293b] space-y-1.5">
            <div class="flex justify-between text-slate-300">
                <span>Opsi Uang Muka (DP 30%):</span>
                <strong class="text-[#F5A623] font-mono">{{ rupiah($quote['dp_amount']) }}</strong>
            </div>
            <div class="flex justify-between text-slate-400 text-[11px]">
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
            <button class="btn btn-primary w-full py-3.5 font-black uppercase tracking-wider text-xs shadow-lg">
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
