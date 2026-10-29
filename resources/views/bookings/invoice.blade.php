<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $booking->code }} - Padaidi Rent Car</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; color: #1e293b !important; }
            .invoice-box { box-shadow: none !important; border: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased p-4 sm:p-8">
    {{-- Bar Tombol Navigasi / Cetak --}}
    <div class="no-print mx-auto max-w-4xl mb-4 flex items-center justify-between">
        <a href="{{ route('bookings.show', $booking) }}" class="text-xs font-semibold text-brand-600 hover:underline">
            &larr; Kembali ke Detail Pesanan
        </a>
        <div class="flex gap-2">
            <button onclick="window.print()" class="btn btn-primary text-xs flex items-center gap-2 py-2 px-4 shadow-sm font-bold uppercase tracking-wider">
                <x-icon name="printer" class="w-4 h-4 text-[#0b1220]" />
                <span>Cetak / Unduh PDF</span>
            </button>
        </div>
    </div>

    {{-- Kertas Faktur / Invoice --}}
    <div class="invoice-box mx-auto max-w-4xl bg-white rounded-2xl shadow-md border border-slate-200 p-8 sm:p-12">
        {{-- Header Invoice --}}
        <div class="flex flex-wrap items-start justify-between gap-6 pb-6 border-b border-slate-200">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/brand/padaidi-logo-light.png') }}" alt="Padaidi Rent Car" class="h-16 w-auto object-contain">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">PADAIDI RENT CAR</h1>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs">
                        Layanan Rental Mobil Lepas Kunci & Driver Profesional Terpercaya di Makassar dan Sekitarnya.
                    </p>
                    <p class="text-xs text-slate-500 mt-1">
                        WhatsApp: 0812-3456-7890 · Email: info@padaidirental.com
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block text-xs font-bold uppercase tracking-wider text-slate-400">FAKTUR SEWA / INVOICE</span>
                <p class="text-2xl font-extrabold text-slate-900 font-mono mt-0.5">INV-{{ $booking->code }}</p>
                <p class="text-xs text-slate-500 mt-1">Tanggal: {{ tanggal($booking->created_at, 'd F Y') }}</p>
                
                {{-- Cap Status Pembayaran --}}
                <div class="mt-2">
                    @if ($booking->isPaidOff())
                        <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-black uppercase tracking-wider text-emerald-800 border border-emerald-300">
                            ✓ LUNAS (PAID)
                        </span>
                    @elseif ($booking->paidAmount() > 0)
                        <span class="inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-black uppercase tracking-wider text-amber-800 border border-amber-300">
                            DP DITERIMA (PARTIAL)
                        </span>
                    @else
                        <span class="inline-block rounded-full bg-rose-100 px-3 py-1 text-xs font-black uppercase tracking-wider text-rose-800 border border-rose-300">
                            BELUM DIBAYAR (UNPAID)
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Data Penyewa & Detail Sewa --}}
        <div class="grid grid-cols-2 gap-8 py-6 text-xs border-b border-slate-200">
            <div>
                <h3 class="font-bold uppercase tracking-wider text-slate-400 mb-2">Tagihan Ditujukan Kepada:</h3>
                <p class="text-sm font-bold text-slate-900">{{ $booking->customer_name }}</p>
                <p class="text-slate-600 mt-0.5">Telepon/WA: {{ $booking->customer_phone }}</p>
                @if ($booking->member)
                    <p class="text-slate-600 mt-0.5">
                        Member: {{ $booking->member->name }} 
                        <span class="font-bold text-brand-600">({{ $booking->member->currentTier()?->name ?? 'Reguler' }})</span>
                    </p>
                @endif
                @if ($booking->delivery_address)
                    <p class="text-slate-600 mt-0.5">Alamat Antar: {{ $booking->delivery_address }}</p>
                @endif
            </div>

            <div>
                <h3 class="font-bold uppercase tracking-wider text-slate-400 mb-2">Detail Penggunaan Armada:</h3>
                <p class="text-sm font-bold text-slate-900">{{ $booking->car->name }} ({{ $booking->car->license_plate ?: 'TBA' }})</p>
                <p class="text-slate-600 mt-0.5">Kategori: {{ $booking->car->category->name }} · {{ ucfirst($booking->car->transmission) }}</p>
                <p class="text-slate-600 mt-0.5">Lokasi Ambil: {{ $booking->pickupLocation->name }}</p>
                <p class="text-slate-600 mt-0.5">
                    Periode: {{ tanggal($booking->pickup_date, 'd M Y') }} s.d. {{ tanggal($booking->return_date, 'd M Y') }} ({{ $booking->days }} Hari)
                </p>
            </div>
        </div>

        {{-- Tabel Rincian Biaya --}}
        <div class="py-6 border-b border-slate-200">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-left font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-2">Deskripsi Layanan</th>
                        <th class="py-2 text-center">Durasi / Qty</th>
                        <th class="py-2 text-right">Tarif Satuan</th>
                        <th class="py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-3 font-medium text-slate-900">
                            Sewa Mobil: {{ $booking->car->name }}
                        </td>
                        <td class="py-3 text-center text-slate-600">{{ $booking->days }} Hari</td>
                        <td class="py-3 text-right text-slate-600">{{ rupiah($booking->daily_price) }}</td>
                        <td class="py-3 text-right font-semibold text-slate-900">{{ rupiah($booking->base_total) }}</td>
                    </tr>
                    @foreach ($booking->addons as $addon)
                        <tr>
                            <td class="py-3 font-medium text-slate-900">
                                Tambahan: {{ $addon->name }}
                            </td>
                            <td class="py-3 text-center text-slate-600">
                                {{ $addon->charge_type === 'per_day' ? $addon->quantity . ' Hari' : '1x Paket' }}
                            </td>
                            <td class="py-3 text-right text-slate-600">{{ rupiah($addon->price) }}</td>
                            <td class="py-3 text-right font-semibold text-slate-900">{{ rupiah($addon->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Kalkulasi Total & Diskon --}}
            <div class="mt-4 flex justify-end">
                <div class="w-full sm:w-72 text-xs space-y-1.5">
                    <div class="flex justify-between py-1 border-t border-slate-100">
                        <span class="text-slate-600">Subtotal Sewa Mobil:</span>
                        <span class="font-semibold">{{ rupiah($booking->base_total) }}</span>
                    </div>
                    @if ($booking->addons->isNotEmpty())
                        <div class="flex justify-between py-1">
                            <span class="text-slate-600">Subtotal Tambahan:</span>
                            <span class="font-semibold">{{ rupiah($booking->addons_total) }}</span>
                        </div>
                    @endif
                    @if ($booking->tier_discount > 0)
                        <div class="flex justify-between py-1 text-amber-700">
                            <span>Diskon Member ({{ $booking->discount_snapshot['tier']['name'] ?? 'Tier' }}):</span>
                            <span class="font-semibold">−{{ rupiah($booking->tier_discount) }}</span>
                        </div>
                    @endif
                    @if ($booking->promo_discount > 0)
                        <div class="flex justify-between py-1 text-emerald-700">
                            <span>Voucher Promo ({{ $booking->promo_code }}):</span>
                            <span class="font-semibold">−{{ rupiah($booking->promo_discount) }}</span>
                        </div>
                    @elseif ($booking->discount > 0 && $booking->tier_discount == 0)
                        <div class="flex justify-between py-1 text-emerald-700">
                            <span>Potongan Diskon:</span>
                            <span class="font-semibold">−{{ rupiah($booking->discount) }}</span>
                        </div>
                    @endif
                    @if ($booking->overtime_fee > 0)
                        <div class="flex justify-between py-1 text-rose-600">
                            <span>Denda Overtime ({{ $booking->overtime_hours }} jam):</span>
                            <span class="font-semibold">+{{ rupiah($booking->overtime_fee) }}</span>
                        </div>
                    @endif
                    @if ($booking->extra_fee > 0)
                        <div class="flex justify-between py-1 text-rose-600">
                            <span>Biaya Tambahan:</span>
                            <span class="font-semibold">+{{ rupiah($booking->extra_fee) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between py-2 border-t-2 border-slate-900 text-sm font-black text-slate-900">
                        <span>TOTAL TAGIHAN:</span>
                        <span>{{ rupiah($booking->grandTotal()) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Riwayat Pembayaran --}}
        <div class="py-6 border-b border-slate-200">
            <h3 class="font-bold uppercase tracking-wider text-slate-400 text-xs mb-3">Catatan Riwayat Pembayaran:</h3>
            @if ($booking->payments->isNotEmpty())
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500 font-semibold">
                            <th class="py-1.5">Ref / ID Transaksi</th>
                            <th class="py-1.5">Waktu</th>
                            <th class="py-1.5">Metode / Kanal</th>
                            <th class="py-1.5 text-right">Nominal</th>
                            <th class="py-1.5 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($booking->payments as $pay)
                            <tr>
                                <td class="py-2 font-mono text-slate-700">{{ $pay->reference_id ?: 'MANUAL-'.$pay->id }}</td>
                                <td class="py-2 text-slate-600">{{ tanggal($pay->paid_at ?? $pay->created_at, 'd/m/Y H:i') }}</td>
                                <td class="py-2 text-slate-700">{{ $pay->channelLabel() }} ({{ $pay->paymentTypeLabel() }})</td>
                                <td class="py-2 text-right font-semibold text-slate-900">{{ rupiah($pay->amount) }}</td>
                                <td class="py-2 text-right font-bold text-xs {{ $pay->isSuccessful() ? 'text-emerald-700' : 'text-slate-500' }}">
                                    {{ $pay->statusLabel() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-xs text-slate-500 italic">Belum ada pembayaran yang tercatat.</p>
            @endif

            <div class="mt-4 flex justify-between items-center text-xs bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div>
                    <span class="text-slate-500">Total Diterima:</span>
                    <strong class="text-emerald-700 font-bold ml-1">{{ rupiah($booking->paidAmount()) }}</strong>
                </div>
                <div>
                    <span class="text-slate-500">Sisa Tagihan yang Harus Dibayar:</span>
                    <strong class="text-brand-700 font-black ml-1 text-sm">{{ rupiah($booking->remainingAmount()) }}</strong>
                </div>
            </div>
        </div>

        {{-- Footer & Tanda Tangan --}}
        <div class="pt-8 grid grid-cols-2 gap-8 text-xs text-slate-500">
            <div>
                <p class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Syarat & Ketentuan Singkat:</p>
                <ol class="mt-1 list-decimal list-inside space-y-1 text-[11px] leading-relaxed">
                    <li>Keterlambatan pengembalian unit dikenakan denda per jam sesuai ketentuan.</li>
                    <li>Unit wajib dikembalikan dalam kondisi bensin sama seperti saat serah terima.</li>
                    <li>Segala bentuk penyalahgunaan unit di luar ketentuan sewa sepenuhnya menjadi tanggung jawab penyewa.</li>
                </ol>
            </div>
            <div class="text-center flex flex-col items-center justify-end">
                <p class="text-slate-400 text-[11px]">Makassar, {{ tanggal(now(), 'd F Y') }}</p>
                <div class="h-16 flex items-center justify-center font-serif text-brand-700 text-lg font-bold italic tracking-wider">
                    Padaidi Official
                </div>
                <div class="w-48 border-b border-slate-300"></div>
                <p class="mt-1 font-bold text-slate-800">Manajemen Padaidi Rent Car</p>
            </div>
        </div>
    </div>
</body>
</html>

