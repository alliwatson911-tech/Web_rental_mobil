@extends('layouts.admin')
@section('title', 'Pesanan '.$booking->code)

@use('App\Enums\BookingStatus')
@php
    $customerWa = preg_replace('/^0/', '62', preg_replace('/\D+/', '', $booking->customer_phone));
@endphp

@section('actions')
    @include('partials.status-badge', ['status' => $booking->status])
@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <section class="card p-5">
            <h2 class="font-bold">Aksi status</h2>
            <p class="mt-1 text-xs text-slate-500">Alur: Menunggu konfirmasi → Dikonfirmasi → Sedang disewa → Selesai. Pembatalan hanya dari Menunggu konfirmasi atau Dikonfirmasi.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @if ($booking->status->canTransitionTo(BookingStatus::Confirmed))
                    <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}" data-confirm="Konfirmasi pesanan ini?">@csrf<button class="btn btn-primary">Konfirmasi</button></form>
                @endif
                @if ($booking->status->canTransitionTo(BookingStatus::Active))
                    <form method="POST" action="{{ route('admin.bookings.pickup', $booking) }}" data-confirm="Tandai mobil sudah diambil pelanggan?">@csrf<button class="btn btn-primary">Tandai diambil</button></form>
                @endif
                @if ($booking->status->canTransitionTo(BookingStatus::Completed))
                    <form method="POST" action="{{ route('admin.bookings.complete', $booking) }}" data-confirm="Tandai pesanan selesai (mobil sudah kembali)?">@csrf<button class="btn btn-success">Tandai selesai</button></form>
                @endif
            </div>
            @if ($booking->status->canTransitionTo(BookingStatus::Cancelled))
                <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}" class="mt-4 flex flex-wrap gap-2" data-confirm="Tolak/batalkan pesanan ini?">
                    @csrf
                    <input class="input max-w-sm" name="reason" placeholder="Alasan penolakan/pembatalan (opsional)">
                    <button class="btn btn-danger">{{ $booking->status === BookingStatus::Pending ? 'Tolak' : 'Batalkan' }}</button>
                </form>
            @endif
            @if (in_array($booking->status, [BookingStatus::Completed, BookingStatus::Cancelled], true))
                <p class="mt-2 text-sm text-slate-500">Pesanan sudah final. {{ $booking->cancel_reason }}</p>
            @endif
        </section>

        <section class="card p-5">
            <h2 class="font-bold">Detail</h2>
            <dl class="mt-3 grid gap-3 text-sm md:grid-cols-2">
                <div><dt class="text-slate-500">Mobil</dt><dd class="font-semibold">{{ $booking->car->name }} · {{ $booking->car->plate_number }}</dd></div>
                <div><dt class="text-slate-500">Lokasi ambil</dt><dd class="font-semibold">{{ $booking->pickupLocation->name }}</dd></div>
                @if ($booking->delivery_address)<div class="md:col-span-2"><dt class="text-slate-500">Alamat antar</dt><dd class="font-semibold">{{ $booking->delivery_address }}</dd></div>@endif
                <div><dt class="text-slate-500">Sewa</dt><dd class="font-semibold">{{ tanggal($booking->pickup_date, 'l, d M Y') }} – {{ tanggal($booking->return_date, 'l, d M Y') }} ({{ $booking->days }} hari)</dd></div>
                <div><dt class="text-slate-500">Dibuat</dt><dd>{{ tanggal($booking->created_at, 'd M Y H:i') }}</dd></div>
                <div><dt class="text-slate-500">Pelanggan</dt><dd class="font-semibold">{{ $booking->customer_name }}</dd></div>
                <div><dt class="text-slate-500">HP</dt><dd><a class="text-brand-600 underline" target="_blank" rel="noopener" href="{{ \App\Support\WhatsApp::link($customerWa, 'Halo '.$booking->customer_name.', kami dari Padaidi Rental Mobil terkait pesanan '.$booking->code.'.') }}">{{ $booking->customer_phone }} (WhatsApp)</a></dd></div>
                @if ($booking->customer_email)<div><dt class="text-slate-500">Email</dt><dd>{{ $booking->customer_email }}</dd></div>@endif
                @if ($booking->customer_address)<div><dt class="text-slate-500">Alamat</dt><dd>{{ $booking->customer_address }}</dd></div>@endif
                @if ($booking->notes)<div class="md:col-span-2"><dt class="text-slate-500">Catatan pelanggan</dt><dd>{{ $booking->notes }}</dd></div>@endif
                <div><dt class="text-slate-500">Riwayat</dt><dd class="text-xs">
                    @if ($booking->confirmed_at)Dikonfirmasi {{ tanggal($booking->confirmed_at, 'd M H:i') }}<br>@endif
                    @if ($booking->picked_up_at)Diambil {{ tanggal($booking->picked_up_at, 'd M H:i') }}<br>@endif
                    @if ($booking->completed_at)Selesai {{ tanggal($booking->completed_at, 'd M H:i') }}<br>@endif
                    @if ($booking->cancelled_at)Dibatalkan {{ tanggal($booking->cancelled_at, 'd M H:i') }}@endif
                </dd></div>
            </dl>
            <form method="POST" action="{{ route('admin.bookings.notes', $booking) }}" class="mt-4">
                @csrf
                <label class="label">Catatan internal admin</label>
                <textarea class="input" name="admin_notes" rows="2">{{ old('admin_notes', $booking->admin_notes) }}</textarea>
                <button class="btn btn-outline mt-2">Simpan catatan</button>
            </form>
        </section>

        <section class="card p-5">
            <h2 class="font-bold">Pembayaran</h2>
            <p class="mt-1 text-sm">Terverifikasi: <strong>{{ rupiah($booking->paidAmount()) }}</strong> dari {{ rupiah($booking->total) }} · Sisa <strong>{{ rupiah($booking->remainingAmount()) }}</strong> {!! $booking->isPaidOff() ? '<span class="badge bg-emerald-100 text-emerald-800">Lunas</span>' : '' !!}</p>
            <div class="mt-3 overflow-x-auto">
                <table class="table-base">
                    <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Metode</th><th>Pengirim</th><th>Bukti</th><th>Status</th><th></th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($booking->payments as $payment)
                        <tr>
                            <td class="whitespace-nowrap">{{ tanggal($payment->paid_at ?? $payment->created_at, 'd M Y H:i') }}</td>
                            <td>{{ rupiah($payment->amount) }}</td>
                            <td>{{ $payment->methodLabel() }}</td>
                            <td>{{ $payment->sender_name }}</td>
                            <td>@if ($payment->proofUrl())<a class="text-brand-600 underline" target="_blank" href="{{ $payment->proofUrl() }}">Lihat</a>@else - @endif</td>
                            <td>{{ $payment->statusLabel() }}@if($payment->verifier)<br><span class="text-xs text-slate-500">oleh {{ $payment->verifier->name }}</span>@endif</td>
                            <td class="whitespace-nowrap">
                                @if ($payment->status === \App\Models\Payment::STATUS_PENDING)
                                    <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="inline">@csrf<button class="btn btn-success px-2 py-1 text-xs">Verifikasi</button></form>
                                    <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="inline" data-confirm="Tolak bukti pembayaran ini?">@csrf<button class="btn btn-danger px-2 py-1 text-xs">Tolak</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-4 text-center text-slate-500">Belum ada pembayaran.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <h3 class="mt-5 text-sm font-bold">Catat pembayaran (langsung terverifikasi)</h3>
            <form method="POST" action="{{ route('admin.payments.store', $booking) }}" enctype="multipart/form-data" class="mt-2 grid gap-3 md:grid-cols-3">
                @csrf
                <div><label class="label">Jumlah (Rp)</label><input class="input" type="number" min="1" name="amount" value="{{ old('amount', $booking->remainingAmount()) }}" required></div>
                <div><label class="label">Metode</label><select class="input" name="method">@foreach (\App\Models\Payment::METHODS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
                <div><label class="label">Tanggal bayar</label><input class="input" type="datetime-local" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required></div>
                <div><label class="label">Nama pengirim</label><input class="input" name="sender_name" value="{{ old('sender_name') }}"></div>
                <div><label class="label">Bukti (opsional)</label><input class="input" type="file" name="proof"></div>
                <div><label class="label">Catatan</label><input class="input" name="notes" value="{{ old('notes') }}"></div>
                <div class="md:col-span-3"><button class="btn btn-primary">Simpan pembayaran</button></div>
            </form>
        </section>
    </div>

    <aside class="card h-fit p-5">
        <h2 class="font-bold">Rincian biaya (harga saat dipesan)</h2>
        <table class="mt-3 w-full text-sm">
            <tr><td class="py-1">Sewa {{ rupiah($booking->daily_price) }} × {{ $booking->days }} hari</td><td class="py-1 text-right">{{ rupiah($booking->base_total) }}</td></tr>
            @foreach ($booking->addons as $line)
                <tr><td class="py-1">{{ $line->name }} ({{ rupiah($line->unit_price) }} × {{ $line->quantity }})</td><td class="py-1 text-right">{{ rupiah($line->subtotal) }}</td></tr>
            @endforeach
            @if ($booking->discount > 0)<tr><td class="py-1">Diskon</td><td class="py-1 text-right">−{{ rupiah($booking->discount) }}</td></tr>@endif
            <tr class="border-t border-slate-200 font-extrabold"><td class="pt-2">Total</td><td class="pt-2 text-right">{{ rupiah($booking->total) }}</td></tr>
        </table>
        @if ($booking->review)
            <div class="mt-4 border-t border-slate-200 pt-3 text-sm"><p class="font-semibold">Ulasan: {{ str_repeat('★', $booking->review->rating) }}</p><p>{{ $booking->review->comment }}</p></div>
        @endif
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline mt-4 w-full">← Semua pesanan</a>
    </aside>
</div>
@endsection
