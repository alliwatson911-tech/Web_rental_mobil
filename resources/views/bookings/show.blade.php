@extends('layouts.app')
@section('title', 'Pesanan '.$booking->code)

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
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-sm text-slate-500">Kode pesanan</p>
        <h1 class="text-3xl font-extrabold tracking-wide text-slate-900">{{ $booking->code }}</h1>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('bookings.invoice', $booking) }}" target="_blank" class="btn btn-outline text-xs flex items-center gap-1.5 py-1.5">
            🖨️ Cetak Invoice
        </a>
        @include('partials.status-badge', ['status' => $booking->status])
    </div>
</div>

@if ($booking->status === \App\Enums\BookingStatus::Pending)
    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <p class="font-semibold">Pesanan menunggu konfirmasi admin.</p>
        <p class="mt-1">Kirim notifikasi ke admin lewat WhatsApp di bawah ini agar pesanan cepat diproses. Pesanan tanpa konfirmasi dan tanpa bukti transfer akan dibatalkan otomatis {{ $business['pending_timeout_hours'] }} jam setelah dibuat ({{ tanggal($booking->created_at->copy()->addHours($business['pending_timeout_hours']), 'd M Y H:i') }} WITA).</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($business['admin_whatsapp'] as $i => $wa)
                <a class="btn btn-success" target="_blank" rel="noopener" href="{{ \App\Support\WhatsApp::link($wa, $waMessage) }}">Kirim WhatsApp ke Admin {{ $i + 1 }}</a>
            @endforeach
        </div>
    </div>
@elseif ($booking->status === \App\Enums\BookingStatus::Cancelled)
    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-100 p-4 text-sm">Pesanan dibatalkan{{ $booking->cancel_reason ? ': '.$booking->cancel_reason : '.' }}</div>
@endif

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <section class="card p-5">
            <h2 class="font-bold text-slate-900">Detail pesanan</h2>
            <dl class="mt-3 grid gap-3 text-sm md:grid-cols-2">
                <div><dt class="text-slate-500">Mobil</dt><dd class="font-semibold">{{ $booking->car->name }} ({{ $booking->car->category->name }}) · {{ $booking->car->transmission === 'automatic' ? 'Matic' : 'Manual' }}</dd></div>
                <div><dt class="text-slate-500">Lokasi ambil</dt><dd class="font-semibold">{{ $booking->pickupLocation->name }}</dd></div>
                @if ($booking->delivery_address)<div class="md:col-span-2"><dt class="text-slate-500">Alamat antar</dt><dd class="font-semibold">{{ $booking->delivery_address }}</dd></div>@endif
                <div><dt class="text-slate-500">Tanggal ambil</dt><dd class="font-semibold">{{ tanggal($booking->pickup_date, 'l, d F Y') }}</dd></div>
                <div><dt class="text-slate-500">Tanggal kembali</dt><dd class="font-semibold">{{ tanggal($booking->return_date, 'l, d F Y') }}</dd></div>
                <div><dt class="text-slate-500">Pemesan</dt><dd class="font-semibold">{{ $booking->customer_name }}</dd></div>
                <div><dt class="text-slate-500">HP/WhatsApp</dt><dd class="font-semibold">{{ $booking->customer_phone }}</dd></div>
            </dl>
        </section>

        {{-- Informasi Penugasan Sopir (jika ada) --}}
        @if ($booking->driver_name)
            @php
                $driverWa = preg_replace('/^0/', '62', preg_replace('/\D+/', '', $booking->driver_phone ?? ''));
            @endphp
            <section class="card border-l-4 border-l-brand-600 p-5 bg-brand-50/30">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-900">Sopir Bertugas</h2>
                        <p class="text-sm text-slate-600 mt-1 font-semibold">{{ $booking->driver_name }}</p>
                        @if ($booking->driver_phone)
                            <p class="text-xs text-slate-500 mt-0.5">Kontak: {{ $booking->driver_phone }}</p>
                        @endif
                    </div>
                    @if ($driverWa)
                        <a class="btn btn-success text-xs" target="_blank" rel="noopener" href="{{ \App\Support\WhatsApp::link($driverWa, 'Halo Mas '.$booking->driver_name.', saya '.$booking->customer_name.' penyewa mobil '.$booking->car->name.' kode '.$booking->code.'.') }}">Hubungi Sopir via WA</a>
                    @endif
                </div>
            </section>
        @endif

        {{-- Serah Terima & Inspeksi Unit (Handover Checklist) --}}
        @if ($booking->start_odometer || $booking->end_odometer)
            <section class="card p-5">
                <h2 class="font-bold text-slate-900">Catatan Serah Terima & Kondisi Kendaraan</h2>
                <div class="mt-3 grid gap-4 text-sm sm:grid-cols-2">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <p class="font-semibold text-slate-800">Saat Penyerahan Unit (Keluar):</p>
                        <p class="mt-1 text-slate-600">Odometer awal: <strong>{{ number_format($booking->start_odometer) }} KM</strong></p>
                        <p class="text-slate-600">Level BBM: <strong>{{ $booking->fuel_out ?: '-' }}</strong></p>
                        @if ($booking->handover_notes)
                            <p class="mt-1 text-xs text-slate-500">Catatan fisik/baret awal: {{ $booking->handover_notes }}</p>
                        @endif
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <p class="font-semibold text-slate-800">Saat Pengembalian Unit (Kembali):</p>
                        @if ($booking->end_odometer)
                            <p class="mt-1 text-slate-600">Odometer akhir: <strong>{{ number_format($booking->end_odometer) }} KM</strong></p>
                            <p class="text-slate-600">Total jarak tempuh: <strong>{{ number_format($booking->distanceDriven()) }} KM</strong></p>
                            <p class="text-slate-600">Level BBM: <strong>{{ $booking->fuel_in ?: '-' }}</strong></p>
                            @if ($booking->return_notes)
                                <p class="mt-1 text-xs text-slate-500">Catatan kembali: {{ $booking->return_notes }}</p>
                            @endif
                        @else
                            <p class="mt-1 text-xs italic text-slate-500">Unit belum dikembalikan ke pool/kantor.</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- Verifikasi Identitas Resmi (e-KYC) --}}
        <section class="card p-5">
            <div class="flex items-center justify-between gap-2">
                <h2 class="font-bold text-slate-900">Verifikasi Identitas Resmi (e-KYC)</h2>
                @if ($booking->isKycComplete())
                    <span class="badge bg-emerald-100 text-emerald-800 text-xs">Dokumen Lengkap</span>
                @else
                    <span class="badge bg-amber-100 text-amber-800 text-xs">Perlu Lengkapi Dokumen</span>
                @endif
            </div>
            <p class="mt-1 text-xs text-slate-500">Untuk keamanan armada dan kepastian sewa (terutama sewa lepas kunci), harap unggah foto e-KTP dan SIM A Anda yang masih aktif. Berkas disimpan privat dan terenkripsi.</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                {{-- KTP --}}
                @php($ktp = $booking->ktpDocument())
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-sm text-slate-900">Foto e-KTP</p>
                        @if ($ktp)
                            <span class="badge {{ $ktp->status === 'verified' ? 'bg-emerald-100 text-emerald-800' : ($ktp->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }} text-xs">{{ $ktp->statusLabel() }}</span>
                        @else
                            <span class="badge bg-slate-100 text-slate-600 text-xs">Belum Diunggah</span>
                        @endif
                    </div>

                    @if ($ktp)
                        <div class="mt-2 text-xs text-slate-600">
                            <p>Terkirim: {{ tanggal($ktp->created_at, 'd M Y H:i') }}</p>
                            <a href="{{ $ktp->documentUrl() }}" target="_blank" class="mt-1 inline-block text-brand-600 underline font-medium">Buka foto KTP</a>
                            @if ($ktp->notes)
                                <p class="mt-1 text-rose-600 italic">Catatan admin: {{ $ktp->notes }}</p>
                            @endif
                        </div>
                    @endif

                    @if (! $ktp || $ktp->status === 'rejected')
                        <form method="POST" action="{{ route('bookings.document', $booking) }}" enctype="multipart/form-data" class="mt-3 space-y-2">
                            @csrf
                            <input type="hidden" name="type" value="ktp">
                            <input class="input text-xs" type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            <button class="btn btn-outline w-full text-xs py-1.5">Unggah Foto KTP</button>
                        </form>
                    @endif
                </div>

                {{-- SIM A --}}
                @php($sim = $booking->simDocument())
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-sm text-slate-900">Foto SIM A</p>
                        @if ($sim)
                            <span class="badge {{ $sim->status === 'verified' ? 'bg-emerald-100 text-emerald-800' : ($sim->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }} text-xs">{{ $sim->statusLabel() }}</span>
                        @else
                            <span class="badge bg-slate-100 text-slate-600 text-xs">Belum Diunggah</span>
                        @endif
                    </div>

                    @if ($sim)
                        <div class="mt-2 text-xs text-slate-600">
                            <p>Terkirim: {{ tanggal($sim->created_at, 'd M Y H:i') }}</p>
                            <a href="{{ $sim->documentUrl() }}" target="_blank" class="mt-1 inline-block text-brand-600 underline font-medium">Buka foto SIM A</a>
                            @if ($sim->notes)
                                <p class="mt-1 text-rose-600 italic">Catatan admin: {{ $sim->notes }}</p>
                            @endif
                        </div>
                    @endif

                    @if (! $sim || $sim->status === 'rejected')
                        <form method="POST" action="{{ route('bookings.document', $booking) }}" enctype="multipart/form-data" class="mt-3 space-y-2">
                            @csrf
                            <input type="hidden" name="type" value="sim">
                            <input class="input text-xs" type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            <button class="btn btn-outline w-full text-xs py-1.5">Unggah Foto SIM A</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        <section class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-900">Pembayaran</h2>
                <span class="text-xs text-slate-500">
                    Terverifikasi: <strong class="text-emerald-700">{{ rupiah($paid) }}</strong> · Sisa: <strong class="text-brand-700">{{ rupiah($booking->remainingAmount()) }}</strong>
                </span>
            </div>

            @if ($canPay)
                <div class="mt-4 rounded-xl border border-brand-200 bg-brand-50/50 p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-bold text-sm text-brand-900">Bayar Otomatis via QRIS / Virtual Account</p>
                        <p class="text-xs text-brand-700 mt-0.5">Konfirmasi instan 24 jam dengan QRIS atau VA BCA, Mandiri, BRI, BNI tanpa upload bukti manual.</p>
                    </div>
                    <a href="{{ route('bookings.pay', $booking) }}" class="btn btn-primary text-xs py-2 px-4 shadow-sm">
                        💳 Bayar Online Sekarang &rarr;
                    </a>
                </div>
            @endif

            @if ($booking->payments->isNotEmpty())
                <div class="mt-4 overflow-x-auto">
                    <table class="table-base">
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
                        <tbody class="divide-y divide-slate-100">
                        @foreach ($booking->payments as $payment)
                            <tr>
                                <td class="text-xs">{{ tanggal($payment->paid_at ?? $payment->created_at, 'd M Y H:i') }}</td>
                                <td class="text-xs">
                                    <span class="font-semibold text-slate-800">{{ $payment->channelLabel() }}</span>
                                    @if ($payment->reference_id)
                                        <span class="block text-[11px] font-mono text-slate-400">{{ $payment->reference_id }}</span>
                                    @endif
                                </td>
                                <td class="text-xs">{{ $payment->paymentTypeLabel() }}</td>
                                <td class="text-xs font-semibold text-slate-900">{{ rupiah($payment->amount) }}</td>
                                <td>
                                    <span class="badge {{ $payment->badgeClass() }} text-xs">
                                        {{ $payment->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-xs">
                                    @if ($payment->status === \App\Models\Payment::STATUS_PENDING && $payment->channel !== \App\Models\Payment::CHANNEL_MANUAL_TRANSFER)
                                        <a href="{{ route('bookings.pay.instruction', [$booking, $payment]) }}" class="text-brand-600 font-semibold underline">
                                            Instruksi Bayar
                                        </a>
                                    @elseif ($payment->proof_path)
                                        <a href="{{ $payment->proofUrl() }}" target="_blank" class="text-brand-600 underline">
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
                <div class="mt-6 pt-4 border-t border-slate-200">
                    <p class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Atau Transfer Bank Manual</p>
                    <p class="text-xs text-slate-600">Transfer ke <strong>{{ $business['bank']['name'] }} {{ $business['bank']['account'] }}</strong> a.n. <strong>{{ $business['bank']['holder'] }}</strong>, lalu kirim foto struk di bawah ini:</p>

                    <form method="POST" action="{{ route('bookings.payment', $booking) }}" enctype="multipart/form-data" class="mt-3 grid gap-3 md:grid-cols-3">
                        @csrf
                        <div>
                            <label class="label text-xs" for="amount">Jumlah transfer (Rp)</label>
                            <input class="input text-xs" type="number" min="1000" step="1" id="amount" name="amount" value="{{ old('amount', $booking->remainingAmount()) }}" required>
                        </div>
                        <div>
                            <label class="label text-xs" for="sender_name">Nama pengirim</label>
                            <input class="input text-xs" id="sender_name" name="sender_name" value="{{ old('sender_name', $booking->customer_name) }}" required>
                        </div>
                        <div>
                            <label class="label text-xs" for="proof">Bukti transfer (JPG/PNG/PDF)</label>
                            <input class="input text-xs" type="file" id="proof" name="proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                        </div>
                        <div class="md:col-span-3">
                            <button class="btn btn-outline text-xs py-1.5">Unggah Bukti Transfer Manual</button>
                        </div>
                    </form>
                </div>
            @endif
        </section>

        @if ($booking->status === \App\Enums\BookingStatus::Completed)
            <section class="card p-5">
                <h2 class="font-bold text-slate-900">Ulasan</h2>
                @if ($booking->review)
                    <p class="mt-2 text-sm">Terima kasih! Nilai Anda: {{ str_repeat('★', $booking->review->rating) }} {{ $booking->review->comment }}</p>
                @else
                    <form method="POST" action="{{ route('bookings.review.store', $booking) }}" class="mt-3 space-y-3">
                        @csrf
                        <div>
                            <label class="label" for="rating">Nilai (1-5)</label>
                            <select class="input" id="rating" name="rating">@for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ $i }} bintang</option>@endfor</select>
                        </div>
                        <div>
                            <label class="label" for="comment">Komentar</label>
                            <textarea class="input" id="comment" name="comment" rows="3"></textarea>
                        </div>
                        <button class="btn btn-primary">Kirim ulasan</button>
                    </form>
                @endif
            </section>
        @endif
    </div>

    <aside class="card h-fit p-5">
        <h2 class="font-bold text-slate-900">Rincian biaya</h2>
        <table class="mt-3 w-full text-sm">
            <tr><td class="py-1">Sewa {{ rupiah($booking->daily_price) }} × {{ $booking->days }} hari</td><td class="py-1 text-right">{{ rupiah($booking->base_total) }}</td></tr>
            @foreach ($booking->addons as $line)
                <tr><td class="py-1">{{ $line->name }} @if($line->charge_type === 'per_day')(× {{ $line->quantity }} hari)@endif</td><td class="py-1 text-right">{{ rupiah($line->subtotal) }}</td></tr>
            @endforeach
            @if ($booking->tier_discount > 0)
                <tr>
                    <td class="py-1 text-amber-800">
                        Diskon Member ({{ $booking->discount_snapshot['tier']['name'] ?? 'Tier' }})
                    </td>
                    <td class="py-1 text-right text-amber-800 font-semibold">
                        −{{ rupiah($booking->tier_discount) }}
                    </td>
                </tr>
            @endif
            @if ($booking->promo_discount > 0)
                <tr>
                    <td class="py-1 text-emerald-800">
                        Kode Promo ({{ $booking->promo_code }})
                    </td>
                    <td class="py-1 text-right text-emerald-800 font-semibold">
                        −{{ rupiah($booking->promo_discount) }}
                    </td>
                </tr>
            @elseif ($booking->discount > 0 && $booking->tier_discount == 0)
                <tr><td class="py-1">Diskon</td><td class="py-1 text-right">−{{ rupiah($booking->discount) }}</td></tr>
            @endif
            @if ($booking->overtime_fee > 0)<tr><td class="py-1 text-rose-600">Denda Overtime ({{ $booking->overtime_hours }} jam)</td><td class="py-1 text-right text-rose-600">+{{ rupiah($booking->overtime_fee) }}</td></tr>@endif
            @if ($booking->extra_fee > 0)<tr><td class="py-1 text-rose-600">Biaya Tambahan ({{ $booking->extra_fee_notes ?: 'Lainnya' }})</td><td class="py-1 text-right text-rose-600">+{{ rupiah($booking->extra_fee) }}</td></tr>@endif
            <tr class="border-t border-slate-200 text-base font-extrabold text-brand-700"><td class="pt-2">Total Tagihan</td><td class="pt-2 text-right">{{ rupiah($booking->grandTotal()) }}</td></tr>
        </table>
        <p class="mt-3 text-xs text-slate-500">Simpan kode pesanan ini. Untuk membuka lagi, gunakan menu <a href="{{ route('bookings.lookup') }}" class="underline">Cek Pesanan</a>.</p>
    </aside>
</div>
@endsection
