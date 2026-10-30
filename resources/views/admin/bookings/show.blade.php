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
                    <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}" data-confirm="Konfirmasi pesanan ini?">@csrf<button class="btn btn-primary">Konfirmasi Pesanan</button></form>
                @endif
            </div>

            {{-- Formulir Serah Terima Unit saat Diambil (Pickup Handover Checklist) --}}
            @if ($booking->status->canTransitionTo(BookingStatus::Active))
                <div class="mt-4 rounded-xl border border-blue-500/30 bg-[#0b1220] p-4">
                    <h3 class="text-sm font-bold text-blue-400">Formulir Serah Terima Unit (Handover Checklist)</h3>
                    <p class="text-xs text-slate-400">Catat kondisi fisik armada sebelum kunci diserahkan ke pelanggan.</p>
                    <form method="POST" action="{{ route('admin.bookings.pickup', $booking) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                        @csrf
                        <div>
                            <label class="label text-slate-300">Odometer Awal (KM)</label>
                            <input class="input" type="number" min="0" name="start_odometer" value="{{ old('start_odometer', $booking->start_odometer) }}" placeholder="Contoh: 45000" required>
                        </div>
                        <div>
                            <label class="label text-slate-300">Indikator BBM Awal</label>
                            <select class="input" name="fuel_out" required>
                                <option value="Full (Penuh)" @selected($booking->fuel_out === 'Full (Penuh)')>Full (Penuh)</option>
                                <option value="3/4" @selected($booking->fuel_out === '3/4')>3/4 Tank</option>
                                <option value="1/2" @selected($booking->fuel_out === '1/2')>1/2 Tank</option>
                                <option value="1/4" @selected($booking->fuel_out === '1/4')>1/4 Tank</option>
                            </select>
                        </div>
                        <div>
                            <label class="label text-slate-300">Nama Sopir (Jika Paket Sopir)</label>
                            <input class="input" name="driver_name" value="{{ old('driver_name', $booking->driver_name) }}" placeholder="Nama sopir bertugas">
                        </div>
                        <div>
                            <label class="label text-slate-300">No. HP Sopir</label>
                            <input class="input" name="driver_phone" value="{{ old('driver_phone', $booking->driver_phone) }}" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label text-slate-300">Catatan Baret / Kondisi Bodi Awal</label>
                            <textarea class="input" name="handover_notes" rows="2" placeholder="Contoh: Baret halus bumper depan kiri, ban serep lengkap, dongkrak ada">{{ old('handover_notes', $booking->handover_notes) }}</textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <button class="btn btn-primary w-full">Konfirmasi Serah Terima & Tandai Diambil</button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Formulir Pemeriksaan Pengembalian Unit (Return Inspection Checklist) --}}
            @if ($booking->status->canTransitionTo(BookingStatus::Completed))
                <div class="mt-4 rounded-xl border border-emerald-500/30 bg-[#0b1220] p-4">
                    <h3 class="text-sm font-bold text-emerald-400">Formulir Pemeriksaan Pengembalian Unit (Return Inspection)</h3>
                    <p class="text-xs text-slate-400">Periksa kondisi odometer, BBM, dan fisik saat mobil dikembalikan.</p>
                    <form method="POST" action="{{ route('admin.bookings.complete', $booking) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                        @csrf
                        <div>
                            <label class="label text-slate-300">Odometer Akhir (KM)</label>
                            <input class="input" type="number" min="{{ $booking->start_odometer ?? 0 }}" name="end_odometer" value="{{ old('end_odometer', $booking->end_odometer) }}" placeholder="Contoh: 45450" required>
                            @if ($booking->start_odometer)
                                <span class="text-xs text-slate-400">KM awal: {{ number_format($booking->start_odometer) }} KM</span>
                            @endif
                        </div>
                        <div>
                            <label class="label text-slate-300">Indikator BBM Kembali</label>
                            <select class="input" name="fuel_in" required>
                                <option value="Full (Penuh)" @selected($booking->fuel_in === 'Full (Penuh)')>Full (Penuh)</option>
                                <option value="3/4" @selected($booking->fuel_in === '3/4')>3/4 Tank</option>
                                <option value="1/2" @selected($booking->fuel_in === '1/2')>1/2 Tank</option>
                                <option value="1/4" @selected($booking->fuel_in === '1/4')>1/4 Tank</option>
                            </select>
                        </div>
                        <div>
                            <label class="label text-slate-300">Keterlambatan (Jam Overtime)</label>
                            <input class="input" type="number" min="0" name="overtime_hours" value="{{ old('overtime_hours', $booking->overtime_hours ?? 0) }}" placeholder="0">
                        </div>
                        <div>
                            <label class="label text-slate-300">Denda Overtime (Rp)</label>
                            <input class="input" type="number" min="0" step="1000" name="overtime_fee" value="{{ old('overtime_fee', $booking->overtime_fee ?? 0) }}" placeholder="0">
                        </div>
                        <div>
                            <label class="label text-slate-300">Biaya Ekstra (Baret / Selisih BBM)</label>
                            <input class="input" type="number" min="0" step="1000" name="extra_fee" value="{{ old('extra_fee', $booking->extra_fee ?? 0) }}" placeholder="0">
                        </div>
                        <div>
                            <label class="label text-slate-300">Keterangan Biaya Ekstra</label>
                            <input class="input" name="extra_fee_notes" value="{{ old('extra_fee_notes', $booking->extra_fee_notes) }}" placeholder="Contoh: Selisih BBM 1/4 bar">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label text-slate-300">Catatan Kondisi Kembali</label>
                            <textarea class="input" name="return_notes" rows="2" placeholder="Catatan kebersihan bodi, interior, perlengkapan">{{ old('return_notes', $booking->return_notes) }}</textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <button class="btn btn-success w-full">Pemeriksaan Selesai & Tandai Selesai</button>
                        </div>
                    </form>
                </div>
            @endif

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

        {{-- Verifikasi Dokumen Identitas (e-KYC) --}}
        <section class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold">Verifikasi Identitas Pelanggan (e-KYC)</h2>
                    <p class="text-xs text-slate-500">Pemeriksaan KTP dan SIM A untuk mitigasi risiko penipuan/penggelapan armada.</p>
                </div>
                @if ($booking->isKycComplete())
                    <span class="badge bg-emerald-100 text-emerald-800">e-KYC Terverifikasi</span>
                @else
                    <span class="badge bg-amber-100 text-amber-800">e-KYC Belum Lengkap</span>
                @endif
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                @forelse ([\App\Models\CustomerDocument::TYPE_KTP, \App\Models\CustomerDocument::TYPE_SIM] as $type)
                    @php($doc = $booking->documents->firstWhere('type', $type))
                    <div class="rounded-xl border border-[#1e293b] bg-[#0b1220] p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-sm text-slate-100">{{ $type === 'ktp' ? 'e-KTP (Identitas)' : 'SIM A (Mengemudi)' }}</h3>
                            @if ($doc)
                                <span class="badge {{ $doc->status === 'verified' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : ($doc->status === 'rejected' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40') }} text-xs">{{ $doc->statusLabel() }}</span>
                            @else
                                <span class="badge bg-slate-800 text-slate-400 border border-[#1e293b] text-xs">Belum Diunggah</span>
                            @endif
                        </div>

                        @if ($doc)
                            <div class="mt-2 text-xs text-slate-300 space-y-1">
                                <p>Diupload: {{ tanggal($doc->created_at, 'd M Y H:i') }}</p>
                                <a href="{{ $doc->documentUrl() }}" target="_blank" class="inline-block text-[#F5A623] font-semibold underline">Buka Dokumen (Aman)</a>
                                @if ($doc->verifier)
                                    <p class="text-slate-400">Diverifikasi oleh: {{ $doc->verifier->name }} ({{ tanggal($doc->verified_at, 'd M H:i') }})</p>
                                @endif
                                @if ($doc->notes)
                                    <p class="text-rose-400 italic">Catatan: {{ $doc->notes }}</p>
                                @endif
                            </div>

                            @if ($doc->status === 'pending')
                                <div class="mt-3 flex gap-2">
                                    <form method="POST" action="{{ route('admin.documents.verify', $doc) }}" class="inline">
                                        @csrf
                                        <button class="btn btn-success px-2 py-1 text-xs">Verifikasi Sah</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.documents.reject', $doc) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="notes" value="Foto dokumen buram/tidak terbaca">
                                        <button class="btn btn-danger px-2 py-1 text-xs">Tolak (Buram)</button>
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
                @if ($booking->driver_name)<div><dt class="text-slate-400">Sopir Bertugas</dt><dd class="font-semibold text-slate-100">{{ $booking->driver_name }} @if($booking->driver_phone)({{ $booking->driver_phone }})@endif</dd></div>@endif
                @if ($booking->start_odometer)<div><dt class="text-slate-400">Odometer Awal</dt><dd class="font-semibold text-slate-100">{{ number_format($booking->start_odometer) }} KM · BBM: {{ $booking->fuel_out ?: '-' }}</dd></div>@endif
                @if ($booking->handover_notes)<div class="md:col-span-2"><dt class="text-slate-400">Catatan Serah Terima (Awal)</dt><dd class="text-xs bg-[#0b1220] border border-[#1e293b] p-2.5 rounded-lg text-slate-200">{{ $booking->handover_notes }}</dd></div>@endif
                @if ($booking->end_odometer)<div><dt class="text-slate-400">Odometer Akhir</dt><dd class="font-semibold text-slate-100">{{ number_format($booking->end_odometer) }} KM (Jarak: {{ number_format($booking->distanceDriven()) }} KM) · BBM: {{ $booking->fuel_in ?: '-' }}</dd></div>@endif
                @if ($booking->return_notes)<div class="md:col-span-2"><dt class="text-slate-400">Catatan Pengembalian</dt><dd class="text-xs bg-[#0b1220] border border-[#1e293b] p-2.5 rounded-lg text-slate-200">{{ $booking->return_notes }}</dd></div>@endif
                <div><dt class="text-slate-400">Riwayat</dt><dd class="text-xs text-slate-300">
                    @if ($booking->confirmed_at)Dikonfirmasi {{ tanggal($booking->confirmed_at, 'd M H:i') }}<br>@endif
                    @if ($booking->picked_up_at)Diambil {{ tanggal($booking->picked_up_at, 'd M H:i') }}<br>@endif
                    @if ($booking->completed_at)Selesai {{ tanggal($booking->completed_at, 'd M H:i') }}<br>@endif
                    @if ($booking->cancelled_at)Dibatalkan {{ tanggal($booking->cancelled_at, 'd M H:i') }}@endif
                </dd></div>
            </dl>
            <form method="POST" action="{{ route('admin.bookings.notes', $booking) }}" class="mt-4">
                @csrf
                <label class="label text-slate-300">Catatan internal admin</label>
                <textarea class="input" name="admin_notes" rows="2">{{ old('admin_notes', $booking->admin_notes) }}</textarea>
                <button class="btn btn-outline mt-2 border-[#1e293b] text-slate-200 hover:text-[#F5A623] hover:border-[#F5A623]">Simpan catatan</button>
            </form>
        </section>

        <section class="card p-5">
            <h2 class="font-bold text-white">Pembayaran</h2>
            <p class="mt-1 text-sm text-slate-300">Terverifikasi: <strong class="text-emerald-400">{{ rupiah($booking->paidAmount()) }}</strong> dari <span class="text-white">{{ rupiah($booking->total) }}</span> · Sisa <strong class="text-[#F5A623]">{{ rupiah($booking->remainingAmount()) }}</strong> {!! $booking->isPaidOff() ? '<span class="badge bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">Lunas</span>' : '' !!}</p>
            <div class="mt-3 overflow-x-auto">
                <table class="table-base">
                    <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Metode</th><th>Pengirim</th><th>Bukti</th><th>Status</th><th></th></tr></thead>
                    <tbody class="divide-y divide-[#1e293b]">
                    @forelse ($booking->payments as $payment)
                        <tr>
                            <td class="whitespace-nowrap text-slate-300">{{ tanggal($payment->paid_at ?? $payment->created_at, 'd M Y H:i') }}</td>
                            <td class="font-bold text-emerald-400">{{ rupiah($payment->amount) }}</td>
                            <td class="text-slate-300">{{ $payment->methodLabel() }}</td>
                            <td class="text-slate-200 font-semibold">{{ $payment->sender_name }}</td>
                            <td>@if ($payment->proofUrl())<a class="text-[#F5A623] underline font-medium" target="_blank" href="{{ $payment->proofUrl() }}">Lihat</a>@else - @endif</td>
                            <td>{{ $payment->statusLabel() }}@if($payment->verifier)<br><span class="text-xs text-slate-400">oleh {{ $payment->verifier->name }}</span>@endif</td>
                            <td class="whitespace-nowrap">
                                @if ($payment->status === \App\Models\Payment::STATUS_PENDING)
                                    <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="inline">@csrf<button class="btn btn-success px-2 py-1 text-xs">Verifikasi</button></form>
                                    <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="inline" data-confirm="Tolak bukti pembayaran ini?">@csrf<button class="btn btn-danger px-2 py-1 text-xs">Tolak</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-4 text-center text-slate-400">Belum ada pembayaran.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <h3 class="mt-5 text-sm font-bold text-white">Catat pembayaran (langsung terverifikasi)</h3>
            <form method="POST" action="{{ route('admin.payments.store', $booking) }}" enctype="multipart/form-data" class="mt-2 grid gap-3 md:grid-cols-3">
                @csrf
                <div><label class="label text-slate-300">Jumlah (Rp)</label><input class="input" type="number" min="1" name="amount" value="{{ old('amount', $booking->remainingAmount()) }}" required></div>
                <div><label class="label text-slate-300">Metode</label><select class="input" name="method">@foreach (\App\Models\Payment::METHODS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
                <div><label class="label text-slate-300">Tanggal bayar</label><input class="input" type="datetime-local" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required></div>
                <div><label class="label text-slate-300">Nama pengirim</label><input class="input" name="sender_name" value="{{ old('sender_name') }}"></div>
                <div><label class="label text-slate-300">Bukti (opsional)</label><input class="input" type="file" name="proof"></div>
                <div><label class="label text-slate-300">Catatan</label><input class="input" name="notes" value="{{ old('notes') }}"></div>
                <div class="md:col-span-3"><button class="btn btn-primary">Simpan pembayaran</button></div>
            </form>
        </section>
    </div>

    <aside class="card h-fit p-5">
        <h2 class="font-bold text-white">Rincian biaya (harga saat dipesan)</h2>
        <table class="mt-3 w-full text-sm text-slate-300">
            <tr><td class="py-1">Sewa {{ rupiah($booking->daily_price) }} × {{ $booking->days }} hari</td><td class="py-1 text-right font-medium text-slate-200">{{ rupiah($booking->base_total) }}</td></tr>
            @foreach ($booking->addons as $line)
                <tr><td class="py-1">{{ $line->name }} ({{ rupiah($line->unit_price) }} × {{ $line->quantity }})</td><td class="py-1 text-right font-medium text-slate-200">{{ rupiah($line->subtotal) }}</td></tr>
            @endforeach
            @if ($booking->discount > 0)<tr><td class="py-1 text-emerald-400">Diskon</td><td class="py-1 text-right text-emerald-400">−{{ rupiah($booking->discount) }}</td></tr>@endif
            @if ($booking->overtime_fee > 0)<tr><td class="py-1 text-rose-400">Denda Overtime ({{ $booking->overtime_hours }} jam)</td><td class="py-1 text-right text-rose-400">+{{ rupiah($booking->overtime_fee) }}</td></tr>@endif
            @if ($booking->extra_fee > 0)<tr><td class="py-1 text-rose-400">Biaya Tambahan ({{ $booking->extra_fee_notes ?: 'Lainnya' }})</td><td class="py-1 text-right text-rose-400">+{{ rupiah($booking->extra_fee) }}</td></tr>@endif
            <tr class="border-t border-[#1e293b] font-extrabold text-white text-base"><td class="pt-3">Total Tagihan</td><td class="pt-3 text-right text-[#F5A623]">{{ rupiah($booking->grandTotal()) }}</td></tr>
        </table>
        @if ($booking->review)
            <div class="mt-4 border-t border-[#1e293b] pt-3 text-sm text-slate-300"><p class="font-semibold text-amber-400">Ulasan: {{ str_repeat('★', $booking->review->rating) }}</p><p class="mt-1 text-slate-400 italic">"{{ $booking->review->comment }}"</p></div>
        @endif
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline mt-4 w-full border-[#1e293b] text-slate-200 hover:text-[#F5A623] hover:border-[#F5A623]">← Semua pesanan</a>
    </aside>
</div>
@endsection
