@extends('layouts.app')
@section('title', 'Riwayat Pesanan Saya - Padaidi Rent Car')

@section('content')
<div class="space-y-6">
    <x-breadcrumb :back-url="route('member.account')" back-text="Kembali ke Dashboard Member" :crumbs="['Dashboard Member' => route('member.account'), 'Riwayat Pesanan' => null]" />

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black uppercase text-slate-900 tracking-wide">Riwayat Pesanan Rental</h1>
            <p class="text-xs text-slate-600 mt-1">Daftar seluruh transaksi sewa mobil Anda di Padaidi Rent Car.</p>
        </div>

        <a href="{{ route('cars.index') }}" class="btn btn-primary text-xs py-2 px-4 uppercase font-black tracking-wider">
            + Sewa Mobil Lagi
        </a>
    </div>

    @if ($bookings->isEmpty())
        <div class="card p-12 text-center bg-white border border-slate-200 shadow-sm">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-2xl font-bold mb-4">
                <x-icon name="car" class="w-8 h-8" />
            </div>
            <h2 class="text-base font-extrabold uppercase text-slate-900 mb-1">Belum Ada Riwayat Sewa</h2>
            <p class="text-xs text-slate-600 max-w-sm mx-auto mb-5 leading-relaxed">
                Anda belum pernah melakukan pemesanan kendaraan. Jelajahi armada pilihan kami dan nikmati diskon otomatis member Anda!
            </p>
            <a href="{{ route('cars.index') }}" class="btn btn-primary text-xs py-2 px-4 uppercase font-bold">
                Lihat Armada Mobil
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($bookings as $booking)
                <div class="card card-hover p-5 bg-white border border-slate-200 shadow-sm transition-all">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-xs font-bold text-slate-900 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded">
                                {{ $booking->booking_code ?: $booking->code }}
                            </span>
                            <span class="text-xs text-slate-600">
                                {{ $booking->created_at->translatedFormat('d M Y, H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (method_exists($booking->status, 'badgeClass'))
                                <span class="badge {{ $booking->status->badgeClass() }} text-xs font-semibold">
                                    {{ $booking->status->label() }}
                                </span>
                            @else
                                <span class="badge badge-accent text-xs">
                                    {{ $booking->status }}
                                </span>
                            @endif
                            @if (!empty($booking->payment_status) && is_object($booking->payment_status))
                                <span class="badge {{ $booking->payment_status->badgeClass() }} text-xs">
                                    {{ $booking->payment_status->label() }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="py-4 grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        {{-- Foto & Nama Mobil --}}
                        <div class="flex items-center gap-3 md:col-span-2">
                            <div class="h-16 w-24 flex-shrink-0 overflow-hidden rounded bg-slate-100 border border-slate-200">
                                @if ($booking->car?->primaryPhoto)
                                    <img src="{{ $booking->car->primaryPhoto->url() }}" 
                                         alt="{{ $booking->car->name }}"
                                         class="h-full w-full object-cover">
                                @else
                                    <div class="grid h-full w-full place-items-center bg-slate-100 text-amber-700">
                                        <x-icon name="car" class="w-6 h-6 opacity-60" />
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm uppercase">
                                    {{ $booking->car?->name ?? ($booking->car?->brand . ' ' . $booking->car?->model) }}
                                </h3>
                                <p class="text-xs text-slate-600 mt-0.5">
                                    {{ $booking->car?->license_plate ?: 'Armada Resmi' }} · {{ $booking->package_type === 'with_driver' ? 'Dengan Sopir' : 'Lepas Kunci' }}
                                </p>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    Ambil di: {{ $booking->pickupLocation?->name ?? 'Pool Pusat Makassar' }}
                                </p>
                            </div>
                        </div>

                        {{-- Periode Sewa --}}
                        <div class="text-xs">
                            <span class="text-slate-500 block mb-0.5">Periode Sewa ({{ $booking->days ?? $booking->total_days }} Hari):</span>
                            <p class="font-bold text-slate-900">
                                {{ tanggal($booking->pickup_date ?? $booking->start_date, 'd M Y') }} s/d {{ tanggal($booking->return_date ?? $booking->end_date, 'd M Y') }}
                            </p>
                        </div>

                        {{-- Total Biaya & Aksi --}}
                        <div class="flex flex-col md:items-end justify-between">
                            <div>
                                <span class="text-slate-500 text-xs block md:text-right">Total Transaksi:</span>
                                <span class="font-black text-amber-700 text-base md:text-right block">
                                    {{ rupiah($booking->grandTotal() ?? $booking->grand_total) }}
                                </span>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('bookings.show', $booking) }}" class="btn btn-outline text-xs px-3 py-1.5 uppercase font-bold tracking-wider">
                                    Detail &amp; Pembayaran &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if (method_exists($bookings, 'links'))
            <div class="mt-6">
                {{ $bookings->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
