@extends('layouts.app')
@section('title', 'Riwayat Pesanan Saya')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('member.dashboard') }}" class="hover:text-brand-700">Akun Saya</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Riwayat Pesanan</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900">Riwayat Pesanan Rental</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar seluruh transaksi sewa mobil Anda di Padaidi Rental Mobil.</p>
        </div>

        <a href="{{ route('cars.index') }}" class="btn btn-primary text-xs">
            + Sewa Mobil Lagi
        </a>
    </div>

    @if ($bookings->isEmpty())
        <div class="card p-12 text-center">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-brand-50 text-brand-600 text-2xl font-bold">
                🚗
            </div>
            <h2 class="mt-4 text-base font-bold text-slate-800">Belum Ada Riwayat Sewa</h2>
            <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                Anda belum pernah melakukan pemesanan kendaraan. Jelajahi armada pilihan kami dan nikmati diskon member Anda!
            </p>
            <div class="mt-5">
                <a href="{{ route('cars.index') }}" class="btn btn-primary text-xs">
                    Lihat Armada Mobil
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($bookings as $booking)
                <div class="card p-5 hover:shadow-md transition">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded">
                                {{ $booking->booking_code }}
                            </span>
                            <span class="text-xs text-slate-400">
                                {{ $booking->created_at->translatedFormat('d M Y, H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge {{ $booking->status->badgeClass() }} text-xs font-semibold">
                                {{ $booking->status->label() }}
                            </span>
                            <span class="badge {{ $booking->payment_status->badgeClass() }} text-xs">
                                {{ $booking->payment_status->label() }}
                            </span>
                        </div>
                    </div>

                    <div class="py-4 grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        {{-- Foto & Nama Mobil --}}
                        <div class="flex items-center gap-3 md:col-span-2">
                            <div class="h-16 w-24 flex-shrink-0 overflow-hidden rounded-xl bg-slate-100 border border-slate-200">
                                @if ($booking->car?->primaryPhoto)
                                    <img src="{{ Storage::url($booking->car->primaryPhoto->photo_path) }}" 
                                         alt="{{ $booking->car->brand }} {{ $booking->car->model }}"
                                         class="h-full w-full object-cover">
                                @else
                                    <div class="grid h-full w-full place-items-center text-xs text-slate-400">
                                        Foto Mobil
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">
                                    {{ $booking->car?->brand }} {{ $booking->car?->model }}
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ $booking->car?->license_plate }} · {{ $booking->package_type === 'with_driver' ? 'Dengan Sopir' : 'Lepas Kunci' }}
                                </p>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Ambil di: {{ $booking->pickupLocation?->name ?? 'Pool Pusat' }}
                                </p>
                            </div>
                        </div>

                        {{-- Periode Sewa --}}
                        <div class="text-xs">
                            <span class="text-slate-400 block mb-0.5">Periode Sewa ({{ $booking->total_days }} Hari):</span>
                            <p class="font-semibold text-slate-700">
                                {{ $booking->start_date->translatedFormat('d M Y') }} s/d {{ $booking->end_date->translatedFormat('d M Y') }}
                            </p>
                        </div>

                        {{-- Total Biaya & Aksi --}}
                        <div class="flex flex-col md:items-end justify-between">
                            <div>
                                <span class="text-slate-400 text-xs block md:text-right">Total Transaksi:</span>
                                <span class="font-extrabold text-brand-700 text-base md:text-right block">
                                    {{ rupiah($booking->grand_total) }}
                                </span>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('bookings.show', $booking) }}" class="btn btn-outline text-xs px-3 py-1.5">
                                    Detail & Pembayaran &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection

