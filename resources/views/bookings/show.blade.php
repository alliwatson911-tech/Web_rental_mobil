@extends('layouts.app')
@section('title', 'Pesanan '.$booking->code.' - Padaidi Rent Car')

@php
    $waMessage = "Halo Admin Padaidi, saya sudah membuat pesanan.\n"
        ."Kode: {$booking->code}\n"
        ."Mobil: {$booking->car->name}\n"
        ."Tanggal: ".tanggal($booking->pickup_date)." s.d. ".tanggal($booking->return_date)." ({$booking->days} hari)\n"
        ."Lokasi: {$booking->pickupLocation->name}\n"
        ."Total: ".rupiah($booking->total)."\n"
        ."Nama: {$booking->customer_name}\n"
        ."Detail (admin): ".route('admin.bookings.show', $booking);
    $paid = $booking->paidAmount();
    $canPay = in_array($booking->status, [\App\Enums\BookingStatus::Pending, \App\Enums\BookingStatus::Confirmed, \App\Enums\BookingStatus::Active], true) && $booking->remainingAmount() > 0;
@endphp

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-[#1e293b] mb-6">
    <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kode pesanan</p>
        <h1 class="text-2xl md:text-3xl font-mono font-black tracking-wider text-[#F5A623] mt-0.5">{{ $booking->code }}</h1>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('bookings.invoice', $booking) }}" target="_blank" class="btn btn-outline text-xs py-2 px-3.5 uppercase font-bold flex items-center gap-1.5">
            <x-icon name="printer" class="w-4 h-4 text-[#F5A623]" />
            <span>Cetak Invoice</span>
        </a>
        @include('partials.status-badge', ['status' => $booking->status])
    </div>
</div>

@if ($booking->status === \App\Enums\BookingStatus::Pending)
    <div class="mb-6 rounded bg-[#111827] border-2 border-amber-500/40 p-5 text-xs text-amber-200">
        <div class="flex items-center gap-2 mb-2">
            <x-icon name="clock" class="w-5 h-5 text-[#F5A623] shrink-0" />
            <p class="font-black uppercase tracking-wider text-white text-sm">Pesanan menunggu konfirmasi admin.</p>
        </div>
        <p class="text-slate-300 leading-relaxed">
            Kirim notifikasi ke admin lewat WhatsApp di bawah ini agar pesanan cepat diproses. Pesanan tanpa konfirmasi dan tanpa bukti transfer akan dibatalkan otomatis {{ $business['pending_timeout_hours'] }} jam setelah dibuat ({{ tanggal($booking->created_at->copy()->addHours($business['pending_timeout_hours']), 'd M Y H:i') }} WITA).
        </p>
        <div class="mt-4 flex flex-wrap gap-2.5">
            @foreach ($business['admin_whatsapp'] as $i => $wa)
                <a class="btn btn-primary text-xs py-2 px-4 uppercase font-black tracking-wider flex items-center gap-1.5" target="_blank" rel="noopener" href="{{ \App\Support\WhatsApp::link($wa, $waMessage) }}">
                    <x-icon name="whatsapp" class="w-4 h-4 text-[#0b1220]" />
                    <span>Kirim WhatsApp ke Admin {{ $i + 1 }}</span>
                </a>
            @endforeach
        </div>
    </div>
@elseif ($booking->status === \App\Enums\BookingStatus::Cancelled)
    <div class="mb-6 rounded border border-rose-500/40 bg-rose-950/60 p-4 text-xs text-rose-200">
        <strong class="font-bold uppercase tracking-wider">Pesanan dibatalkan</strong>{{ $booking->cancel_reason ? ': '.$booking->cancel_reason : '.' }}
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-3 items-start">
    <div class="space-y-6 lg:col-span-2">
        {{-- Detail Pesanan --}}
        <section class="card card-accent-top p-6 md:p-8 bg-[#111827] border border-[#1e293b]">
            <h2 class="font-extrabold text-base uppercase text-white tracking-wide mb-4">Detail pesanan</h2>
            <dl class="grid gap-3.5 text-xs md:grid-cols-2 pt-2 border-t border-[#1e293b]">
                <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                    <dt class="text-[10px] uppercase font-bold text-slate-400">Mobil</dt>
                    <dd class="text-sm font-bold text-white mt-1">{{ $booking->car->name }} ({{ $booking->car->category->name }}) · {{ $booking->car->transmission === 'automatic' ? 'Matic' : 'Manual' }}</dd>
                </div>
                <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                    <dt class="text-[10px] uppercase font-bold text-slate-400">Lokasi ambil</dt>
                    <dd class="text-sm font-bold text-white mt-1">{{ $booking->pickupLocation->name }}</dd>
                </div>
                @if ($booking->delivery_address)
                    <div class="md:col-span-2 bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                        <dt class="text-[10px] uppercase font-bold text-slate-400">Alamat antar</dt>
                        <dd class="text-sm font-bold text-amber-300 mt-1">{{ $booking->delivery_address }}</dd>
                    </div>
                @endif
                <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                    <dt class="text-[10px] uppercase font-bold text-slate-400">Tanggal ambil</dt>
                    <dd class="text-sm font-bold text-white mt-1">{{ tanggal($booking->pickup_date, 'l, d F Y') }}</dd>
                </div>
                <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                    <dt class="text-[10px] uppercase font-bold text-slate-400">Tanggal kembali</dt>
                    <dd class="text-sm font-bold text-white mt-1">{{ tanggal($booking->return_date, 'l, d F Y') }}</dd>
                </div>
                <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                    <dt class="text-[10px] uppercase font-bold text-slate-400">Pemesan</dt>
                    <dd class="text-sm font-bold text-white mt-1">{{ $booking->customer_name }}</dd>
                </div>
                <div class="bg-[#0b1220] p-3.5 rounded border border-[#1e293b]">
                    <dt class="text-[10px] uppercase font-bold text-slate-400">HP/WhatsApp</dt>
                    <dd class="text-sm font-bold text-white mt-1">{{ $booking->customer_phone }}</dd>
                </div>
            </dl>
        </section>

        {{-- Informasi Sopir --}}
        @if ($booking->driver_name)
            @php
                $driverWa = preg_replace('/^0/', '62', preg_replace('/\D+/', '', $booking->driver_phone ?? ''));
            @endphp
            <section class="card p-6 bg-[#111827] border border-[#1e293b] border-l-4 border-l-[#F5A623]">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="font-extrabold text-sm uppercase text-white tracking-wide">Sopir Bertugas</h2>
                        <p class="text-base text-white mt-1 font-black">{{ $booking->driver_name }}</p>
                        @if ($booking->driver_phone)
                            <p class="text-xs text-slate-400 mt-0.5">Kontak: {{ $booking->driver_phone }}</p>
                        @endif
                    </div>
                    @if ($driverWa)
                        <a class="btn btn-primary text-xs py-2 px-4 uppercase font-bold flex items-center gap-1.5" target="_blank" rel="noopener" href="{{ \App\Support\WhatsApp::link($driverWa, 'Halo Mas '.$booking->driver_name.', saya '.$booking->customer_name.' penyewa mobil '.$booking->car->name.' kode '.$booking->code.'.') }}">
                            <x-icon name="whatsapp" class="w-4 h-4 text-[#0b1220]" />
                            <span>Hubungi Sopir via WA</span>
                        </a>
                    @endif
                </div>
            </section>
        @endif

        {{-- Serah Terima Kendaraan --}}
        @if ($booking->start_odometer || $booking->end_odometer)
            <section class="card p-6 md:p-8 bg-[#111827] border border-[#1e293b]">
                <h2 class="font-extrabold text-base uppercase text-white tracking-wide mb-4">Catatan Serah Terima &amp; Kondisi Kendaraan</h2>
                <div class="grid gap-4 text-xs sm:grid-cols-2 pt-2 border-t border-[#1e293b]">
                    <div class="rounded bg-[#0b1220] border border-[#1e293b] p-4 space-y-1.5">
                        <p class="font-extrabold uppercase text-[#F5A623] text-[11px]">Saat Penyerahan Unit (Keluar):</p>
                        <p class="text-slate-300">Odometer awal: <strong class="text-white">{{ number_format($booking->start_odometer) }} KM</strong></p>
                        <p class="text-slate-300">Level BBM: <strong class="text-white">{{ $booking->fuel_out ?: '-' }}</strong></p>
                        @if ($booking->handover_notes)
                            <p class="text-[11px] text-slate-400 pt-1 border-t border-[#1e293b]">Catatan awal: {{ $booking->handover_notes }}</p>
                        @endif
                    </div>
                    <div class="rounded bg-[#0b1220] border border-[#1e293b] p-4 space-y-1.5">
                        <p class="font-extrabold uppercase text-[#F5A623] text-[11px]">Saat Pengembalian Unit (Kembali):</p>
                        @if ($booking->end_odometer)
                            <p class="text-slate-300">Odometer akhir: <strong class="text-white">{{ number_format($booking->end_odometer) }} KM</strong></p>
                            <p class="text-slate-300">Total jarak tempuh: <strong class="text-white">{{ number_format($booking->distanceDriven()) }} KM</strong></p>
                            <p class="text-slate-300">Level BBM: <strong class="text-white">{{ $booking->fuel_in ?: '-' }}</strong></p>
                            @if ($booking->return_notes)
                                <p class="text-[11px] text-slate-400 pt-1 border-t border-[#1e293b]">Catatan kembali: {{ $booking->return_notes }}</p>
                            @endif
                        @else
                            <p class="text-xs italic text-slate-500 pt-2">Unit belum dikembalikan ke pool/kantor.</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- Verifikasi Identitas Resmi (e-KYC) --}}
        <section class="card p-6 md:p-8 bg-[#111827] border border-[#1e293b]">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                <h2 class="font-extrabold text-base uppercase text-white tracking-wide">Verifikasi Identitas Resmi (e-KYC)</h2>
                @if ($booking->isKycComplete())
                    <span class="badge badge-success text-xs">Dokumen Lengkap</span>
                @else
                    <span class="badge badge-warning text-xs">Perlu Lengkapi Dokumen</span>
                @endif
            </div>
            <p class="text-xs text-slate-400 mb-5 leading-relaxed">
                Untuk keamanan armada dan kepastian sewa (terutama sewa lepas kunci), harap unggah foto e-KTP dan SIM A Anda yang masih aktif. Berkas disimpan privat dan terenkripsi.
            </p>

            <div class="grid gap-4 sm:grid-cols-2 pt-2 border-t border-[#1e293b]">
                {{-- KTP --}}
                @php($ktp = $booking->ktpDocument())
                <div class="rounded bg-[#0b1220] border border-[#1e293b] p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <p class="font-extrabold text-xs uppercase text-white">Foto e-KTP</p>
                            @if ($ktp)
                                <span class="badge {{ $ktp->status === 'verified' ? 'badge-success' : ($ktp->status === 'rejected' ? 'badge-danger' : 'badge-warning') }} text-[10px]">{{ $ktp->statusLabel() }}</span>
                            @else
                                <span class="badge bg-slate-800 text-slate-400 border border-slate-700 text-[10px]">Belum Diunggah</span>
                            @endif
                        </div>

                        @if ($ktp)
                            <div class="mt-2.5 text-xs text-slate-400 space-y-1">
                                <p>Terkirim: <span class="text-slate-300">{{ tanggal($ktp->created_at, 'd M Y H:i') }}</span></p>
                                <a href="{{ $ktp->documentUrl() }}" target="_blank" class="mt-1 inline-block text-amber-400 underline font-bold hover:text-white">Buka foto KTP ↗</a>
                                @if ($ktp->notes)
                                    <p class="mt-1 text-rose-400 italic bg-rose-950/40 p-1.5 rounded">Catatan admin: {{ $ktp->notes }}</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if (! $ktp || $ktp->status === 'rejected')
                        <form method="POST" action="{{ route('bookings.document', $booking) }}" enctype="multipart/form-data" class="mt-4 border-t border-[#1e293b] pt-3 space-y-2">
                            @csrf
                            <input type="hidden" name="type" value="ktp">
                            <input class="input text-xs" type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            <button class="btn btn-primary w-full text-xs py-2 uppercase font-black">Unggah Foto KTP</button>
                        </form>
                    @endif
                </div>

                {{-- SIM A --}}
                @php($sim = $booking->simDocument())
                <div class="rounded bg-[#0b1220] border border-[#1e293b] p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <p class="font-extrabold text-xs uppercase text-white">Foto SIM A</p>
                            @if ($sim)
                                <span class="badge {{ $sim->status === 'verified' ? 'badge-success' : ($sim->status === 'rejected' ? 'badge-danger' : 'badge-warning') }} text-[10px]">{{ $sim->statusLabel() }}</span>
                            @else
                                <span class="badge bg-slate-800 text-slate-400 border border-slate-700 text-[10px]">Belum Diunggah</span>
                            @endif
                        </div>

                        @if ($sim)
                            <div class="mt-2.5 text-xs text-slate-400 space-y-1">
                                <p>Terkirim: <span class="text-slate-300">{{ tanggal($sim->created_at, 'd M Y H:i') }}</span></p>
                                <a href="{{ $sim->documentUrl() }}" target="_blank" class="mt-1 inline-block text-amber-400 underline font-bold hover:text-white">Buka foto SIM A ↗</a>
                                @if ($sim->notes)
                                    <p class="mt-1 text-rose-400 italic bg-rose-950/40 p-1.5 rounded">Catatan admin: {{ $sim->notes }}</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if (! $sim || $sim->status === 'rejected')
                        <form method="POST" action="{{ route('bookings.document', $booking) }}" enctype="multipart/form-data" class="mt-4 border-t border-[#1e293b] pt-3 space-y-2">
                            @csrf
                            <input type="hidden" name="type" value="sim">
                            <input class="input text-xs" type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            <button class="btn btn-primary w-full text-xs py-2 uppercase font-black">Unggah Foto SIM A</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        {{-- Bagian Pembayaran --}}
        <section class="card p-6 md:p-8 bg-[#111827] border border-[#1e293b]">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h2 class="font-extrabold text-base uppercase text-white tracking-wide">Pembayaran</h2>
                <div class="text-xs text-slate-400">
                    Terverifikasi: <strong class="text-emerald-400">{{ rupiah($paid) }}</strong> · Sisa: <strong class="text-[#F5A623]">{{ rupiah($booking->remainingAmount()) }}</strong>
                </div>
            </div>

            @if ($canPay)
                <div class="rounded bg-[#0b1220] border-2 border-[#F5A623]/40 p-5 flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div>
                        <p class="font-black uppercase text-sm text-white tracking-wide">Bayar Otomatis via QRIS / Virtual Account</p>
                        <p class="text-xs text-slate-300 mt-1">Konfirmasi instan 24 jam dengan QRIS atau VA BCA, Mandiri, BRI, BNI tanpa upload bukti manual.</p>
                    </div>
                    <a href="{{ route('bookings.pay', $booking) }}" class="btn btn-primary text-xs py-2.5 px-5 uppercase font-black tracking-wider shadow-lg flex items-center gap-2">
                        <x-icon name="credit-card" class="w-4 h-4 text-[#0b1220]" />
                        <span>Bayar Online Sekarang &rarr;</span>
                    </a>
                </div>
            @endif

            @if ($booking->payments->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="table-base text-xs">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Metode / Ref</th>
                                <th>Jenis</th>
                                <th>Nominal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#1e293b]">
                        @foreach ($booking->payments as $payment)
                            <tr>
                                <td class="text-slate-400">{{ tanggal($payment->paid_at ?? $payment->created_at, 'd M Y H:i') }}</td>
                                <td>
                                    <span class="font-bold text-white">{{ $payment->channelLabel() }}</span>
                                    @if ($payment->reference_id)
                                        <span class="block text-[11px] font-mono text-slate-500">{{ $payment->reference_id }}</span>
                                    @endif
                                </td>
                                <td class="text-slate-300">{{ $payment->paymentTypeLabel() }}</td>
                                <td class="font-extrabold text-white">{{ rupiah($payment->amount) }}</td>
                                <td>
                                    <span class="badge {{ $payment->badgeClass() }} text-[10px]">
                                        {{ $payment->statusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    @if ($payment->status === \App\Models\Payment::STATUS_PENDING && $payment->channel !== \App\Models\Payment::CHANNEL_MANUAL_TRANSFER)
                                        <a href="{{ route('bookings.pay.instruction', [$booking, $payment]) }}" class="btn btn-outline text-[11px] py-1 px-2.5 uppercase font-bold">
                                            Instruksi
                                        </a>
                                    @elseif ($payment->proof_path)
                                        <a href="{{ $payment->proofUrl() }}" target="_blank" class="text-amber-400 hover:text-amber-300 font-bold underline">
                                            Lihat Bukti
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($canPay)
                <div class="mt-6 pt-5 border-t border-[#1e293b]">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Atau Transfer Bank Manual</p>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Transfer ke rekening <strong>{{ $business['bank']['name'] }} {{ $business['bank']['account'] }}</strong> a.n. <strong>{{ $business['bank']['holder'] }}</strong>, lalu unggah foto struk bukti transfer di bawah ini:
                    </p>

                    <form method="POST" action="{{ route('bookings.payment', $booking) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-3">
                        @csrf
                        <div>
                            <label class="label text-xs text-slate-300" for="amount">Jumlah transfer (Rp)</label>
                            <input class="input text-xs" type="number" min="1000" step="1" id="amount" name="amount" value="{{ old('amount', $booking->remainingAmount()) }}" required>
                        </div>
                        <div>
                            <label class="label text-xs text-slate-300" for="sender_name">Nama pengirim</label>
                            <input class="input text-xs" id="sender_name" name="sender_name" value="{{ old('sender_name', $booking->customer_name) }}" required>
                        </div>
                        <div>
                            <label class="label text-xs text-slate-300" for="proof">Bukti transfer (JPG/PNG/PDF)</label>
                            <input class="input text-xs" type="file" id="proof" name="proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                        </div>
                        <div class="md:col-span-3 pt-2">
                            <button type="submit" class="btn btn-outline w-full text-xs py-2.5 uppercase font-bold">
                                Unggah Bukti Transfer Manual
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </section>

        {{-- Ulasan Pelanggan --}}
        @if ($booking->status === \App\Enums\BookingStatus::Completed)
            <section class="card p-6 md:p-8 bg-[#111827] border border-[#1e293b]">
                <h2 class="font-extrabold text-base uppercase text-white tracking-wide mb-3">Ulasan</h2>
                @if ($booking->review)
                    <div class="rounded bg-[#0b1220] p-4 border border-[#1e293b] text-xs">
                        <span class="text-[#F5A623] tracking-widest">{{ str_repeat('★', $booking->review->rating) }}</span>
                        <p class="text-slate-300 mt-2">{{ $booking->review->comment }}</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('bookings.review.store', $booking) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="label text-slate-300" for="rating">Nilai Layanan (1-5 Bintang)</label>
                            <select class="input text-xs" id="rating" name="rating">
                                @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} Bintang</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="label text-slate-300" for="comment">Komentar Pengalaman</label>
                            <textarea class="input text-xs" id="comment" name="comment" rows="3" placeholder="Bagaimana kondisi mobil dan pelayanan kami?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary text-xs py-2 px-5 uppercase font-black">
                            Kirim ulasan
                        </button>
                    </form>
                @endif
            </section>
        @endif
    </div>

    {{-- Sidebar Rincian Biaya --}}
    <aside class="card card-accent-top p-6 bg-[#111827] border border-[#1e293b] shadow-2xl h-fit">
        <h2 class="font-extrabold text-base uppercase text-white tracking-wide mb-4">Rincian biaya</h2>
        <table class="w-full text-xs">
            <tbody class="divide-y divide-[#1e293b]">
                <tr>
                    <td class="py-2 text-slate-300">Sewa {{ rupiah($booking->daily_price) }} × {{ $booking->days }} hari</td>
                    <td class="py-2 text-right font-bold text-white">{{ rupiah($booking->base_total) }}</td>
                </tr>
                @foreach ($booking->addons as $line)
                    <tr>
                        <td class="py-2 text-slate-300">{{ $line->name }} @if($line->charge_type === 'per_day')(× {{ $line->quantity }} hari)@endif</td>
                        <td class="py-2 text-right font-bold text-white">{{ rupiah($line->subtotal) }}</td>
                    </tr>
                @endforeach
                @if ($booking->tier_discount > 0)
                    <tr>
                        <td class="py-2 text-amber-400 font-medium">
                            Diskon Member ({{ $booking->discount_snapshot['tier']['name'] ?? 'Tier' }})
                        </td>
                        <td class="py-2 text-right text-amber-400 font-extrabold">
                            −{{ rupiah($booking->tier_discount) }}
                        </td>
                    </tr>
                @endif
                @if ($booking->promo_discount > 0)
                    <tr>
                        <td class="py-2 text-emerald-400 font-medium">
                            Kode Promo ({{ $booking->promo_code }})
                        </td>
                        <td class="py-2 text-right text-emerald-400 font-extrabold">
                            −{{ rupiah($booking->promo_discount) }}
                        </td>
                    </tr>
                @elseif ($booking->discount > 0 && $booking->tier_discount == 0)
                    <tr>
                        <td class="py-2 text-emerald-400 font-medium">Diskon</td>
                        <td class="py-2 text-right text-emerald-400 font-extrabold">−{{ rupiah($booking->discount) }}</td>
                    </tr>
                @endif
                @if ($booking->overtime_fee > 0)
                    <tr>
                        <td class="py-2 text-rose-400">Denda Overtime ({{ $booking->overtime_hours }} jam)</td>
                        <td class="py-2 text-right text-rose-400 font-bold">+{{ rupiah($booking->overtime_fee) }}</td>
                    </tr>
                @endif
                @if ($booking->extra_fee > 0)
                    <tr>
                        <td class="py-2 text-rose-400">Biaya Tambahan ({{ $booking->extra_fee_notes ?: 'Lainnya' }})</td>
                        <td class="py-2 text-right text-rose-400 font-bold">+{{ rupiah($booking->extra_fee) }}</td>
                    </tr>
                @endif
                <tr class="border-t-2 border-[#1e293b] text-sm font-black text-white">
                    <td class="pt-3 uppercase">Total Tagihan</td>
                    <td class="pt-3 text-right text-[#F5A623] text-base">{{ rupiah($booking->grandTotal()) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="mt-5 p-3.5 rounded bg-[#0b1220] border border-[#1e293b] text-[11px] text-slate-400 leading-relaxed">
            Simpan kode pesanan ini untuk melacak status melalui menu <a href="{{ route('bookings.lookup') }}" class="text-amber-400 font-bold underline hover:text-white">Cek Pesanan</a>.
        </div>
    </aside>
</div>
@endsection
