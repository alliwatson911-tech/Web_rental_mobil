@extends('layouts.admin')
@section('title', 'Pesanan '.$booking->code)

@use('App\Enums\BookingStatus')
@php
    $customerWa = preg_replace('/^0/', '62', preg_replace('/\D+/', '', $booking->customer_phone));
@endphp

@section('actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Daftar Pesanan</a>
        @include('partials.status-badge', ['status' => $booking->status])
    </div>
@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <section class="card p-5 bg-white border border-slate-200 shadow-sm rounded-xl">
            <h2 class="font-bold text-slate-900 text-base">Aksi status</h2>
            <p class="mt-1 text-xs text-slate-600">Alur: Menunggu konfirmasi → Dikonfirmasi → Sedang disewa → Selesai. Pembatalan hanya dari Menunggu konfirmasi atau Dikonfirmasi.</p>
            
            <div class="mt-3 flex flex-wrap gap-2">
                @if ($booking->status->canTransitionTo(BookingStatus::Confirmed))
                    <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}" data-confirm="Konfirmasi pesanan ini?">@csrf<button class="btn btn-primary">Konfirmasi Pesanan</button></form>
                @endif
            </div>

            {{-- Formulir Serah Terima Unit saat Diambil (Pickup Handover Checklist) --}}
            @if ($booking->status->canTransitionTo(BookingStatus::Active))
                <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50/50 p-4">
                    <h3 class="text-sm font-bold text-blue-900">Formulir Serah Terima Unit (Handover Checklist)</h3>
                    <p class="text-xs text-slate-600">Catat kondisi fisik armada sebelum kunci diserahkan ke pelanggan.</p>
                    <form method="POST" action="{{ route('admin.bookings.pickup', $booking) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                        @csrf
                        <div>
                            <label class="label text-slate-700 font-bold">Odometer Awal (KM)</label>
                            <input class="input bg-white border-slate-300 text-slate-900" type="number" min="0" max="9999999" name="start_odometer" value="{{ old('start_odometer', $booking->start_odometer) }}" placeholder="Contoh: 45000" required>
                        </div>
                        <div>
                            <label class="label text-slate-700 font-bold">Indikator BBM Awal</label>
                            <select class="input bg-white border-slate-300 text-slate-900" name="fuel_out" required>
                                <option value="Full (Penuh)" @selected($booking->fuel_out === 'Full (Penuh)')>Full (Penuh)</option>
                                <option value="3/4" @selected($booking->fuel_out === '3/4')>3/4 Tank</option>
                                <option value="1/2" @selected($booking->fuel_out === '1/2')>1/2 Tank</option>
                                <option value="1/4" @selected($booking->fuel_out === '1/4')>1/4 Tank</option>
                            </select>
                        </div>
                        <div>
                            <label class="label text-slate-700 font-bold">Nama Sopir (Jika Paket Sopir)</label>
                            <input class="input bg-white border-slate-300 text-slate-900" name="driver_name" value="{{ old('driver_name', $booking->driver_name) }}" placeholder="Nama sopir bertugas" maxlength="100">
                        </div>
                        <div>
                            <label class="label text-slate-700 font-bold">No. HP Sopir</label>
                            <input class="input bg-white border-slate-300 text-slate-900" name="driver_phone" value="{{ old('driver_phone', $booking->driver_phone) }}" placeholder="08xxxxxxxxxx" maxlength="20">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label text-slate-700 font-bold">Catatan Baret / Kondisi Bodi Awal</label>
                            <textarea class="input bg-white border-slate-300 text-slate-900" name="handover_notes" rows="2" placeholder="Contoh: Baret halus bumper depan kiri, ban serep lengkap, dongkrak ada" maxlength="1000">{{ old('handover_notes', $booking->handover_notes) }}</textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <button class="btn btn-primary w-full shadow-sm">Konfirmasi Serah Terima & Tandai Diambil</button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Formulir Pemeriksaan Pengembalian Unit (Return Inspection Checklist) --}}
            @if ($booking->status->canTransitionTo(BookingStatus::Completed))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                    <h3 class="text-sm font-bold text-emerald-900">Formulir Pemeriksaan Pengembalian Unit (Return Inspection)</h3>
                    <p class="text-xs text-slate-600">Periksa kondisi odometer, BBM, dan fisik saat mobil dikembalikan.</p>
                    <form method="POST" action="{{ route('admin.bookings.complete', $booking) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                        @csrf
                        <div>
                            <label class="label text-slate-700 font-bold">Odometer Akhir (KM)</label>
                            <input class="input bg-white border-slate-300 text-slate-900" type="number" min="{{ $booking->start_odometer ?? 0 }}" max="9999999" name="end_odometer" value="{{ old('end_odometer', $booking->end_odometer) }}" placeholder="Contoh: 45450" required>
                            @if ($booking->start_odometer)
                                <span class="text-xs text-slate-500 font-medium">KM awal: {{ number_format($booking->start_odometer) }} KM</span>
                            @endif
                        </div>
                        <div>
                            <label class="label text-slate-700 font-bold">Indikator BBM Kembali</label>
                            <select class="input bg-white border-slate-300 text-slate-900" name="fuel_in" required>
                                <option value="Full (Penuh)" @selected($booking->fuel_in === 'Full (Penuh)')>Full (Penuh)</option>
                                <option value="3/4" @selected($booking->fuel_in === '3/4')>3/4 Tank</option>
                                <option value="1/2" @selected($booking->fuel_in === '1/2')>1/2 Tank</option>
                                <option value="1/4" @selected($booking->fuel_in === '1/4')>1/4 Tank</option>
                            </select>
                        </div>
                        <div>
                            <label class="label text-slate-700 font-bold">Keterlambatan (Jam Overtime)</label>
                            <input class="input bg-white border-slate-300 text-slate-900" type="number" min="0" max="720" name="overtime_hours" value="{{ old('overtime_hours', $booking->overtime_hours ?? 0) }}" placeholder="0">
                        </div>
                        <div>
                            <label class="label text-slate-700 font-bold">Denda Overtime (Rp)</label>
                            <input class="input bg-white border-slate-300 text-slate-900" type="number" min="0" max="1000000000" step="1000" name="overtime_fee" value="{{ old('overtime_fee', $booking->overtime_fee ?? 0) }}" placeholder="0">
                        </div>
                        <div>
                            <label class="label text-slate-700 font-bold">Biaya Ekstra (Baret / Selisih BBM)</label>
                            <input class="input bg-white border-slate-300 text-slate-900" type="number" min="0" max="1000000000" step="1000" name="extra_fee" value="{{ old('extra_fee', $booking->extra_fee ?? 0) }}" placeholder="0">
                        </div>
                        <div>
                            <label class="label text-slate-700 font-bold">Keterangan Biaya Ekstra</label>
                            <input class="input bg-white border-slate-300 text-slate-900" name="extra_fee_notes" value="{{ old('extra_fee_notes', $booking->extra_fee_notes) }}" placeholder="Contoh: Selisih BBM 1/4 bar" maxlength="255">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label text-slate-700 font-bold">Catatan Kondisi Kembali</label>
                            <textarea class="input bg-white border-slate-300 text-slate-900" name="return_notes" rows="2" placeholder="Catatan kebersihan bodi, interior, perlengkapan" maxlength="1000">{{ old('return_notes', $booking->return_notes) }}</textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <button class="btn btn-success w-full shadow-sm">Pemeriksaan Selesai & Tandai Selesai</button>
                        </div>
                    </form>
                </div>
            @endif

            @if ($booking->status->canTransitionTo(BookingStatus::Cancelled))
                <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}" class="mt-4 flex flex-wrap gap-2" data-confirm="Tolak/batalkan pesanan ini?">
                    @csrf
                    <input class="input max-w-sm bg-white border-slate-300 text-slate-900" name="reason" placeholder="Alasan penolakan/pembatalan (opsional)" maxlength="255">
                    <button class="btn btn-danger">{{ $booking->status === BookingStatus::Pending ? 'Tolak' : 'Batalkan' }}</button>
                </form>
            @endif
            @if (in_array($booking->status, [BookingStatus::Completed, BookingStatus::Cancelled], true))
                <p class="mt-2 text-sm text-slate-600">Pesanan sudah final. {{ $booking->cancel_reason }}</p>
            @endif
        </section>

        {{-- Verifikasi Dokumen Identitas (e-KYC) --}}
        <section class="card p-5 bg-white border border-slate-200 shadow-sm rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-base">Verifikasi Identitas Pelanggan (e-KYC)</h2>
                    <p class="text-xs text-slate-600">Pemeriksaan KTP dan SIM A untuk mitigasi risiko penipuan/penggelapan armada.</p>
                </div>
                @if ($booking->isKycComplete())
                    <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold">e-KYC Terverifikasi</span>
                @else
                    <span class="badge bg-amber-100 text-amber-800 border border-amber-300 font-bold">e-KYC Belum Lengkap</span>
                @endif
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                @forelse ([\App\Models\CustomerDocument::TYPE_KTP, \App\Models\CustomerDocument::TYPE_SIM] as $type)
                    @php($doc = $booking->documents->firstWhere('type', $type))
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-sm text-slate-900">{{ $type === 'ktp' ? 'e-KTP (Identitas)' : 'SIM A (Mengemudi)' }}</h3>
                            @if ($doc)
                                <span class="badge {{ $doc->status === 'verified' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($doc->status === 'rejected' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-amber-100 text-amber-800 border border-amber-300') }} text-xs font-bold">{{ $doc->statusLabel() }}</span>
                            @else
                                <span class="badge bg-slate-200 text-slate-700 border border-slate-300 text-xs font-semibold">Belum Diunggah</span>
                            @endif
                        </div>

                        @if ($doc)
                            <div class="mt-2 text-xs text-slate-700 space-y-1">
                                <p>Diupload: {{ tanggal($doc->created_at, 'd/m/Y H:i') }}</p>
                                <a href="{{ $doc->documentUrl() }}" target="_blank" class="inline-block text-amber-700 font-bold underline hover:text-amber-900">Buka Dokumen (Aman) ↗</a>
                                @if ($doc->verifier)
                                    <p class="text-slate-500 font-medium">Diverifikasi oleh: {{ $doc->verifier->name }} ({{ tanggal($doc->verified_at, 'd/m/Y H:i') }})</p>
                                @endif
                                @if ($doc->notes)
                                    <p class="text-rose-700 italic font-semibold">Catatan: {{ $doc->notes }}</p>
                                @endif
                            </div>

                            @if ($doc->status === 'pending')
                                <div class="mt-3 flex gap-2">
                                    <form method="POST" action="{{ route('admin.documents.verify', $doc) }}" class="inline">
                                        @csrf
                                        <button class="btn btn-success px-2.5 py-1 text-xs font-bold text-white shadow-sm">Verifikasi Sah</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.documents.reject', $doc) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="notes" value="Foto dokumen buram/tidak terbaca">
                                        <button class="btn btn-danger px-2.5 py-1 text-xs font-bold text-white shadow-sm">Tolak (Buram)</button>
                                    </form>
                                </div>
                            @endif
                        @else
                            <p class="mt-2 text-xs text-slate-500 italic">Pelanggan belum mengunggah dokumen ini.</p>
                        @endif
                    </div>
                @empty
                @endforelse
            </div>
        </section>

        <section class="card p-5 bg-white border border-slate-200 shadow-sm rounded-xl">
            <h2 class="font-bold text-slate-900 text-base">Detail Pesanan</h2>
            <dl class="mt-3 grid gap-3 text-sm md:grid-cols-2">
                <div><dt class="text-slate-500 text-xs font-bold uppercase">Mobil</dt><dd class="font-bold text-slate-900">{{ $booking->car->name }} · {{ $booking->car->plate_number }}</dd></div>
                <div><dt class="text-slate-500 text-xs font-bold uppercase">Lokasi ambil</dt><dd class="font-semibold text-slate-800">{{ $booking->pickupLocation->name }}</dd></div>
                @if ($booking->delivery_address)<div class="md:col-span-2"><dt class="text-slate-500 text-xs font-bold uppercase">Alamat antar</dt><dd class="font-semibold text-slate-800">{{ $booking->delivery_address }}</dd></div>@endif
                <div><dt class="text-slate-500 text-xs font-bold uppercase">Sewa</dt><dd class="font-semibold text-slate-800">{{ tanggal($booking->pickup_date, 'l, d/m/Y') }} – {{ tanggal($booking->return_date, 'l, d/m/Y') }} ({{ $booking->days }} hari)</dd></div>
                <div><dt class="text-slate-500 text-xs font-bold uppercase">Dibuat</dt><dd class="text-slate-800">{{ tanggal($booking->created_at, 'd/m/Y H:i') }}</dd></div>
                <div><dt class="text-slate-500 text-xs font-bold uppercase">Pelanggan</dt><dd class="font-bold text-slate-900">{{ $booking->customer_name }}</dd></div>
                <div><dt class="text-slate-500 text-xs font-bold uppercase">HP</dt><dd><a class="text-amber-700 hover:text-amber-900 font-bold underline" target="_blank" rel="noopener" href="{{ \App\Support\WhatsApp::link($customerWa, 'Halo '.$booking->customer_name.', kami dari Padaidi Rental Mobil terkait pesanan '.$booking->code.'.') }}">{{ $booking->customer_phone }} (WhatsApp)</a></dd></div>
                @if ($booking->customer_email)<div><dt class="text-slate-500 text-xs font-bold uppercase">Email</dt><dd class="text-slate-800">{{ $booking->customer_email }}</dd></div>@endif
                @if ($booking->customer_address)<div><dt class="text-slate-500 text-xs font-bold uppercase">Alamat</dt><dd class="text-slate-800">{{ $booking->customer_address }}</dd></div>@endif
                @if ($booking->notes)<div class="md:col-span-2"><dt class="text-slate-500 text-xs font-bold uppercase">Catatan pelanggan</dt><dd class="text-slate-800 font-medium">{{ $booking->notes }}</dd></div>@endif
                @if ($booking->driver_name)<div><dt class="text-slate-500 text-xs font-bold uppercase">Sopir Bertugas</dt><dd class="font-bold text-slate-900">{{ $booking->driver_name }} @if($booking->driver_phone)({{ $booking->driver_phone }})@endif</dd></div>@endif
                @if ($booking->start_odometer)<div><dt class="text-slate-500 text-xs font-bold uppercase">Odometer Awal</dt><dd class="font-bold text-slate-900">{{ number_format($booking->start_odometer) }} KM · BBM: {{ $booking->fuel_out ?: '-' }}</dd></div>@endif
                @if ($booking->handover_notes)<div class="md:col-span-2"><dt class="text-slate-500 text-xs font-bold uppercase">Catatan Serah Terima (Awal)</dt><dd class="text-xs bg-slate-50 border border-slate-200 p-2.5 rounded-lg text-slate-800 font-medium">{{ $booking->handover_notes }}</dd></div>@endif
                @if ($booking->end_odometer)<div><dt class="text-slate-500 text-xs font-bold uppercase">Odometer Akhir</dt><dd class="font-bold text-slate-900">{{ number_format($booking->end_odometer) }} KM (Jarak: {{ number_format($booking->distanceDriven()) }} KM) · BBM: {{ $booking->fuel_in ?: '-' }}</dd></div>@endif
                @if ($booking->return_notes)<div class="md:col-span-2"><dt class="text-slate-500 text-xs font-bold uppercase">Catatan Pengembalian</dt><dd class="text-xs bg-slate-50 border border-slate-200 p-2.5 rounded-lg text-slate-800 font-medium">{{ $booking->return_notes }}</dd></div>@endif
                <div><dt class="text-slate-500 text-xs font-bold uppercase">Riwayat</dt><dd class="text-xs text-slate-700">
                    @if ($booking->confirmed_at)Dikonfirmasi {{ tanggal($booking->confirmed_at, 'd/m/Y H:i') }}<br>@endif
                    @if ($booking->picked_up_at)Diambil {{ tanggal($booking->picked_up_at, 'd/m/Y H:i') }}<br>@endif
                    @if ($booking->completed_at)Selesai {{ tanggal($booking->completed_at, 'd/m/Y H:i') }}<br>@endif
                    @if ($booking->cancelled_at)Dibatalkan {{ tanggal($booking->cancelled_at, 'd/m/Y H:i') }}@endif
                </dd></div>
            </dl>
            <form method="POST" action="{{ route('admin.bookings.notes', $booking) }}" class="mt-4">
                @csrf
                <label class="label text-slate-700 font-bold">Catatan internal admin</label>
                <textarea class="input bg-white border-slate-300 text-slate-900" name="admin_notes" rows="2" maxlength="2000">{{ old('admin_notes', $booking->admin_notes) }}</textarea>
                <button class="btn btn-outline mt-2 border-slate-300 text-slate-700 hover:text-amber-800 hover:border-amber-400 font-bold text-xs">Simpan catatan</button>
            </form>
        </section>

        <section class="card p-5 bg-white border border-slate-200 shadow-sm rounded-xl">
            <h2 class="font-bold text-slate-900 text-base">Pembayaran</h2>
            <p class="mt-1 text-sm text-slate-600">Terverifikasi: <strong class="text-emerald-700 font-black">{{ rupiah($booking->paidAmount()) }}</strong> dari <span class="text-slate-900 font-black">{{ rupiah($booking->total) }}</span> · Sisa <strong class="text-amber-700 font-black">{{ rupiah($booking->remainingAmount()) }}</strong> {!! $booking->isPaidOff() ? '<span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold">Lunas</span>' : '' !!}</p>
            <div class="mt-3 overflow-x-auto">
                <table class="table-base w-full text-xs min-w-[750px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap">Tanggal</th>
                            <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap">Jumlah</th>
                            <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap">Metode</th>
                            <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap">Pengirim</th>
                            <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap">Bukti</th>
                            <th class="py-3 px-4 text-center font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap">Status</th>
                            <th class="py-3 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($booking->payments as $payment)
                        <tr class="hover:bg-amber-50/40 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap text-slate-700 font-medium">{{ tanggal($payment->paid_at ?? $payment->created_at, 'd/m/Y H:i') }}</td>
                            <td class="py-3 px-4 font-black text-emerald-700 whitespace-nowrap">{{ rupiah($payment->amount) }}</td>
                            <td class="py-3 px-4 text-slate-700 font-medium whitespace-nowrap">{{ $payment->methodLabel() }}</td>
                            <td class="py-3 px-4 text-slate-900 font-bold whitespace-nowrap">{{ $payment->sender_name }}</td>
                            <td class="py-3 px-4 whitespace-nowrap">@if ($payment->proofUrl())<a class="text-amber-700 hover:text-amber-900 underline font-bold" target="_blank" href="{{ $payment->proofUrl() }}">Lihat ↗</a>@else - @endif</td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider whitespace-nowrap border shadow-xs {{ $payment->badgeClass() }}">
                                    {{ $payment->statusLabel() }}
                                </span>
                                @if($payment->verifier)<br><span class="text-[10px] text-slate-500 font-medium">oleh {{ $payment->verifier->name }}</span>@endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-right">
                                @if ($payment->status === \App\Models\Payment::STATUS_PENDING)
                                    <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="inline">@csrf<button class="btn btn-success px-2.5 py-1 text-xs font-bold text-white shadow-sm">Verifikasi</button></form>
                                    <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="inline" data-confirm="Tolak bukti pembayaran ini?">@csrf<button class="btn btn-danger px-2.5 py-1 text-xs font-bold text-white shadow-sm">Tolak</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-4 text-center text-slate-500 italic bg-slate-50/50">Belum ada pembayaran.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <h3 class="mt-5 text-sm font-bold text-slate-900">Catat pembayaran (langsung terverifikasi)</h3>
            <form method="POST" action="{{ route('admin.payments.store', $booking) }}" enctype="multipart/form-data" class="mt-2 grid gap-3 md:grid-cols-3">
                @csrf
                <div><label class="label text-slate-700 font-bold">Jumlah (Rp)</label><input class="input bg-white border-slate-300 text-slate-900 font-bold" type="number" min="1" max="1000000000" name="amount" value="{{ old('amount', $booking->remainingAmount()) }}" required></div>
                <div><label class="label text-slate-700 font-bold">Metode</label><select class="input bg-white border-slate-300 text-slate-900" name="method">@foreach (\App\Models\Payment::METHODS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
                <div><label class="label text-slate-700 font-bold">Tanggal bayar</label><input class="input bg-white border-slate-300 text-slate-900" type="datetime-local" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required></div>
                <div><label class="label text-slate-700 font-bold">Nama pengirim</label><input class="input bg-white border-slate-300 text-slate-900" name="sender_name" value="{{ old('sender_name') }}" maxlength="100"></div>
                <div><label class="label text-slate-700 font-bold">Bukti (opsional)</label><input class="input bg-white border-slate-300 text-slate-900" type="file" name="proof"></div>
                <div><label class="label text-slate-700 font-bold">Catatan</label><input class="input bg-white border-slate-300 text-slate-900" name="notes" value="{{ old('notes') }}" maxlength="500"></div>
                <div class="md:col-span-3"><button class="btn btn-primary shadow-sm font-black uppercase text-xs">Simpan pembayaran</button></div>
            </form>
        </section>
    </div>

    <aside class="card h-fit p-5 bg-white border border-slate-200 shadow-sm rounded-xl">
        <h2 class="font-bold text-slate-900 text-base">Rincian biaya (harga saat dipesan)</h2>
        <table class="mt-3 w-full text-sm text-slate-700">
            <tr><td class="py-1">Sewa {{ rupiah($booking->daily_price) }} × {{ $booking->days }} hari</td><td class="py-1 text-right font-semibold text-slate-900">{{ rupiah($booking->base_total) }}</td></tr>
            @foreach ($booking->addons as $line)
                <tr><td class="py-1">{{ $line->name }} ({{ rupiah($line->unit_price) }} × {{ $line->quantity }})</td><td class="py-1 text-right font-semibold text-slate-900">{{ rupiah($line->subtotal) }}</td></tr>
            @endforeach
            @if ($booking->discount > 0)<tr><td class="py-1 text-emerald-700 font-medium">Diskon</td><td class="py-1 text-right text-emerald-700 font-bold">−{{ rupiah($booking->discount) }}</td></tr>@endif
            @if ($booking->overtime_fee > 0)<tr><td class="py-1 text-rose-700 font-medium">Denda Overtime ({{ $booking->overtime_hours }} jam)</td><td class="py-1 text-right text-rose-700 font-bold">+{{ rupiah($booking->overtime_fee) }}</td></tr>@endif
            @if ($booking->extra_fee > 0)<tr><td class="py-1 text-rose-700 font-medium">Biaya Tambahan ({{ $booking->extra_fee_notes ?: 'Lainnya' }})</td><td class="py-1 text-right text-rose-700 font-bold">+{{ rupiah($booking->extra_fee) }}</td></tr>@endif
            <tr class="border-t border-slate-200 font-black text-slate-900 text-base"><td class="pt-3">Total Tagihan</td><td class="pt-3 text-right text-amber-800 font-black">{{ rupiah($booking->grandTotal()) }}</td></tr>
        </table>
        @if ($booking->review)
            <div class="mt-4 border-t border-slate-200 pt-3 text-sm text-slate-700"><p class="font-bold text-amber-700">Ulasan: {{ str_repeat('★', $booking->review->rating) }}</p><p class="mt-1 text-slate-600 italic">"{{ $booking->review->comment }}"</p></div>
        @endif
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline mt-4 w-full border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-xs flex items-center justify-center">← Semua pesanan</a>
    </aside>
</div>
@endsection
