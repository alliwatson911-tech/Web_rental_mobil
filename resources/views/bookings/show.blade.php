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
    @include('partials.status-badge', ['status' => $booking->status])
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
                <div><dt class="text-slate-500">Mobil</dt><dd class="font-semibold">{{ $booking->car->name }} ({{ $booking->car->category->name }})</dd></div>
                <div><dt class="text-slate-500">Lokasi ambil</dt><dd class="font-semibold">{{ $booking->pickupLocation->name }}</dd></div>
                @if ($booking->delivery_address)<div class="md:col-span-2"><dt class="text-slate-500">Alamat antar</dt><dd class="font-semibold">{{ $booking->delivery_address }}</dd></div>@endif
                <div><dt class="text-slate-500">Tanggal ambil</dt><dd class="font-semibold">{{ tanggal($booking->pickup_date, 'l, d F Y') }}</dd></div>
                <div><dt class="text-slate-500">Tanggal kembali</dt><dd class="font-semibold">{{ tanggal($booking->return_date, 'l, d F Y') }}</dd></div>
                <div><dt class="text-slate-500">Pemesan</dt><dd class="font-semibold">{{ $booking->customer_name }}</dd></div>
                <div><dt class="text-slate-500">HP/WhatsApp</dt><dd class="font-semibold">{{ $booking->customer_phone }}</dd></div>
            </dl>
        </section>

        <section class="card p-5">
            <h2 class="font-bold text-slate-900">Pembayaran</h2>
            <p class="mt-2 text-sm">Transfer ke <strong>{{ $business['bank']['name'] }} {{ $business['bank']['account'] }}</strong> a.n. <strong>{{ $business['bank']['holder'] }}</strong>, lalu unggah bukti transfer di sini.</p>
            <p class="mt-2 text-sm">Sudah terverifikasi: <strong>{{ rupiah($paid) }}</strong> · Sisa: <strong>{{ rupiah($booking->remainingAmount()) }}</strong></p>

            @if ($booking->payments->isNotEmpty())
                <div class="mt-3 overflow-x-auto">
                    <table class="table-base">
                        <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Status</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach ($booking->payments as $payment)
                            <tr><td>{{ tanggal($payment->paid_at ?? $payment->created_at, 'd M Y H:i') }}</td><td>{{ rupiah($payment->amount) }}</td><td>{{ $payment->statusLabel() }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($canPay)
                <form method="POST" action="{{ route('bookings.payment', $booking) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-3">
                    @csrf
                    <div>
                        <label class="label" for="amount">Jumlah transfer (Rp)</label>
                        <input class="input" type="number" min="1000" step="1" id="amount" name="amount" value="{{ old('amount', $booking->remainingAmount()) }}" required>
                    </div>
                    <div>
                        <label class="label" for="sender_name">Nama pengirim</label>
                        <input class="input" id="sender_name" name="sender_name" value="{{ old('sender_name', $booking->customer_name) }}" required>
                    </div>
                    <div>
                        <label class="label" for="proof">Bukti transfer (JPG/PNG/PDF)</label>
                        <input class="input" type="file" id="proof" name="proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    </div>
                    <div class="md:col-span-3"><button class="btn btn-primary">Unggah bukti transfer</button></div>
                </form>
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
            @if ($booking->discount > 0)<tr><td class="py-1">Diskon</td><td class="py-1 text-right">−{{ rupiah($booking->discount) }}</td></tr>@endif
            <tr class="border-t border-slate-200 text-base font-extrabold text-brand-700"><td class="pt-2">Total</td><td class="pt-2 text-right">{{ rupiah($booking->total) }}</td></tr>
        </table>
        <p class="mt-3 text-xs text-slate-500">Simpan kode pesanan ini. Untuk membuka lagi, gunakan menu <a href="{{ route('bookings.lookup') }}" class="underline">Cek Pesanan</a>.</p>
    </aside>
</div>
@endsection
