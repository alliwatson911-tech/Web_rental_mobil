@extends('layouts.admin')
@section('title', 'Transaksi & Pembayaran')

@section('actions')
    <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn btn-outline text-xs flex items-center gap-1.5 py-2 px-3.5 h-[42px] min-h-[42px] font-bold uppercase tracking-wider text-slate-700 border-slate-300 hover:bg-slate-100 hover:text-slate-900 shadow-sm">
        <x-icon name="download" class="w-4 h-4 text-amber-600" />
        <span>Ekspor Data (CSV)</span>
    </a>
@endsection

@section('content')
<p class="text-xs text-slate-600 -mt-3 mb-6 font-medium">Monitoring pembayaran gateway (QRIS, VA Bank), mutasi transfer manual, dan kelola refund.</p>

{{-- Kartu Ringkasan Metrik --}}
<div class="grid gap-4 sm:grid-cols-3 mb-6">
    <div class="card p-5 bg-white border border-slate-200 border-l-4 border-l-emerald-500 shadow-sm rounded-xl">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Diterima / Lunas</p>
        <p class="mt-1 text-2xl font-black text-emerald-700">{{ rupiah($totalPaid) }}</p>
        <p class="text-[11px] text-slate-500 mt-1">Status Paid &amp; Verified</p>
    </div>
    <div class="card p-5 bg-white border border-slate-200 border-l-4 border-l-amber-500 shadow-sm rounded-xl">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Menunggu Pembayaran</p>
        <p class="mt-1 text-2xl font-black text-amber-700">{{ rupiah($totalPending) }}</p>
        <p class="text-[11px] text-slate-500 mt-1">Status Pending / Tagihan Aktif</p>
    </div>
    <div class="card p-5 bg-white border border-slate-200 border-l-4 border-l-rose-500 shadow-sm rounded-xl">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Refund / Pengembalian</p>
        <p class="mt-1 text-2xl font-black text-rose-700">{{ rupiah($totalRefunded) }}</p>
        <p class="text-[11px] text-slate-500 mt-1">Dana yang telah dikembalikan</p>
    </div>
</div>

{{-- Filter & Pencarian Sejajar & Format dd/mm/yyyy --}}
<div class="card p-5 mb-6 bg-white border border-slate-200 shadow-sm rounded-xl">
    <form method="GET" action="{{ route('admin.payments.index') }}" class="grid gap-3.5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 text-xs items-end">
        <div class="flex flex-col justify-end">
            <label class="label text-[11px] font-extrabold uppercase tracking-wider text-slate-700 min-h-[22px] flex items-center mb-1.5" for="filter_q">
                Cari Transaksi / Pesanan
            </label>
            <input type="text" id="filter_q" name="q" value="{{ request('q') }}" placeholder="Ref ID, Kode, Nama..." class="input text-xs bg-white border border-slate-300 text-slate-900 rounded px-3 h-[42px] min-h-[42px] focus:border-[#F5A623] focus:ring-1 focus:ring-[#F5A623] w-full font-medium" maxlength="100">
        </div>
        <div class="flex flex-col justify-end">
            <label class="label text-[11px] font-extrabold uppercase tracking-wider text-slate-700 min-h-[22px] flex items-center mb-1.5" for="filter_status">
                Status Pembayaran
            </label>
            <select id="filter_status" name="status" class="input text-xs bg-white border border-slate-300 text-slate-900 rounded px-3 h-[42px] min-h-[42px] focus:border-[#F5A623] focus:ring-1 focus:ring-[#F5A623] w-full font-semibold">
                <option value="semua" {{ request('status') === 'semua' ? 'selected' : '' }}>Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid (Lunas Gateway)</option>
                <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified (Terverifikasi)</option>
                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded (Penuh)</option>
                <option value="partially_refunded" {{ request('status') === 'partially_refunded' ? 'selected' : '' }}>Partially Refunded</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired (Kedaluwarsa)</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed (Gagal)</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
            </select>
        </div>
        <div class="flex flex-col justify-end">
            <label class="label text-[11px] font-extrabold uppercase tracking-wider text-slate-700 min-h-[22px] flex items-center mb-1.5" for="filter_channel">
                Metode / Kanal
            </label>
            <select id="filter_channel" name="channel" class="input text-xs bg-white border border-slate-300 text-slate-900 rounded px-3 h-[42px] min-h-[42px] focus:border-[#F5A623] focus:ring-1 focus:ring-[#F5A623] w-full font-semibold">
                <option value="">Semua Metode</option>
                <option value="qris" {{ request('channel') === 'qris' ? 'selected' : '' }}>QRIS (Instant)</option>
                <option value="va_bca" {{ request('channel') === 'va_bca' ? 'selected' : '' }}>BCA Virtual Account</option>
                <option value="va_mandiri" {{ request('channel') === 'va_mandiri' ? 'selected' : '' }}>Mandiri Virtual Account</option>
                <option value="va_bni" {{ request('channel') === 'va_bni' ? 'selected' : '' }}>BNI Virtual Account</option>
                <option value="va_bri" {{ request('channel') === 'va_bri' ? 'selected' : '' }}>BRI BRIVA</option>
                <option value="va_permata" {{ request('channel') === 'va_permata' ? 'selected' : '' }}>Permata VA</option>
                <option value="manual_transfer" {{ request('channel') === 'manual_transfer' ? 'selected' : '' }}>Transfer Bank Manual</option>
                <option value="cash" {{ request('channel') === 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
            </select>
        </div>
        <div class="flex flex-col justify-end">
            <label class="label text-[11px] font-extrabold uppercase tracking-wider text-slate-700 min-h-[22px] flex items-center mb-1.5" for="start_date_display">
                Tanggal Dari (dd/mm/yyyy)
            </label>
            <x-date-picker id="start_date" name="start_date" :value="request('start_date')" placeholder="dd/mm/yyyy" class="text-xs h-[42px] min-h-[42px] bg-white border border-slate-300 text-slate-900" />
        </div>
        <div class="flex flex-col justify-end">
            <label class="label text-[11px] font-extrabold uppercase tracking-wider text-slate-700 min-h-[22px] flex items-center mb-1.5" for="end_date_display">
                Tanggal Sampai (dd/mm/yyyy)
            </label>
            <x-date-picker id="end_date" name="end_date" :value="request('end_date')" placeholder="dd/mm/yyyy" class="text-xs h-[42px] min-h-[42px] bg-white border border-slate-300 text-slate-900" />
        </div>
        <div class="flex flex-col justify-end">
            <div class="min-h-[22px] mb-1.5"></div>
            <div class="flex items-center gap-2 h-[42px]">
                <button type="submit" class="btn btn-primary text-xs h-[42px] min-h-[42px] px-4 font-black uppercase tracking-wider flex-1 shadow-sm flex items-center justify-center gap-1.5 text-[#0b1220]">
                    <x-icon name="search" class="w-3.5 h-3.5 text-[#0b1220]" />
                    <span>Filter</span>
                </button>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline text-xs h-[42px] min-h-[42px] px-3.5 font-bold uppercase tracking-wider flex items-center justify-center text-slate-700 border-slate-300 hover:bg-slate-100 hover:text-slate-900 shadow-sm">
                    Reset
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Tabel Pembayaran Sejajar & Berlatar Putih Bersih --}}
<div class="card overflow-x-auto bg-white border border-slate-200 shadow-sm rounded-xl p-0 mb-6">
    <table class="table-base w-full min-w-[1150px] text-xs border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th scope="col" class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[130px]">Waktu Transaksi</th>
                <th scope="col" class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[190px]">Referensi / ID</th>
                <th scope="col" class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[140px]">Kode Pesanan</th>
                <th scope="col" class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[130px]">Pelanggan</th>
                <th scope="col" class="py-3.5 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[170px]">Metode / Kanal</th>
                <th scope="col" class="py-3.5 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[120px]">Nominal</th>
                <th scope="col" class="py-3.5 px-4 text-center font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[160px]">Status</th>
                <th scope="col" class="py-3.5 px-4 text-right font-black text-slate-700 uppercase tracking-wider text-[11px] whitespace-nowrap min-w-[110px]">Tindakan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @forelse ($payments as $payment)
            <tr class="hover:bg-amber-50/40 transition-colors">
                <td class="py-3.5 px-4 whitespace-nowrap align-middle">
                    <span class="font-bold text-slate-900 block text-xs whitespace-nowrap">
                        {{ tanggal($payment->created_at, 'd/m/Y') }}
                    </span>
                    <span class="text-[11px] text-slate-500 font-mono block mt-0.5 whitespace-nowrap">
                        {{ tanggal($payment->created_at, 'H:i') }} WITA
                    </span>
                    @if ($payment->paid_at)
                        <span class="inline-flex items-center gap-1 mt-1 text-[10px] text-emerald-800 bg-emerald-50 border border-emerald-300 font-bold px-2 py-0.5 rounded whitespace-nowrap">
                            <span>Lunas:</span>
                            <span>{{ tanggal($payment->paid_at, 'H:i') }}</span>
                        </span>
                    @endif
                </td>
                <td class="py-3.5 px-4 align-middle whitespace-nowrap">
                    <span class="font-mono font-black text-slate-900 block text-xs whitespace-nowrap select-all">{{ $payment->reference_id ?: 'MANUAL-'.$payment->id }}</span>
                    @if ($payment->gateway_name)
                        <span class="inline-block text-[10px] uppercase font-bold text-slate-500 tracking-wider mt-0.5 whitespace-nowrap">{{ $payment->gateway_name }}</span>
                    @endif
                </td>
                <td class="py-3.5 px-4 align-middle whitespace-nowrap">
                    <a class="font-mono font-black text-amber-700 hover:text-amber-900 hover:underline block text-xs whitespace-nowrap" href="{{ route('admin.bookings.show', $payment->booking) }}">
                        {{ $payment->booking->code }}
                    </a>
                    <span class="block text-[11px] text-slate-600 font-semibold mt-0.5 whitespace-nowrap">{{ $payment->booking->car->name }}</span>
                </td>
                <td class="py-3.5 px-4 align-middle whitespace-nowrap">
                    <span class="font-black text-slate-900 block text-xs whitespace-nowrap">{{ $payment->booking->customer_name }}</span>
                    @if ($payment->sender_name && $payment->sender_name !== $payment->booking->customer_name)
                        <span class="block text-[10px] text-slate-500 font-medium mt-0.5 whitespace-nowrap">Pengirim: {{ $payment->sender_name }}</span>
                    @endif
                </td>
                <td class="py-3.5 px-4 align-middle whitespace-nowrap">
                    <span class="font-black text-slate-900 block text-xs whitespace-nowrap">{{ $payment->channelLabel() }}</span>
                    <span class="block text-[11px] text-slate-600 font-medium mt-0.5 whitespace-nowrap">{{ $payment->paymentTypeLabel() }}</span>
                    @if ($payment->va_number)
                        <span class="inline-flex items-center font-mono text-[10px] text-amber-900 font-bold bg-amber-50 border border-amber-300 px-1.5 py-0.5 rounded mt-1 whitespace-nowrap">VA: {{ $payment->va_number }}</span>
                    @endif
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap text-right align-middle">
                    <span class="font-black text-slate-900 text-xs block whitespace-nowrap">{{ rupiah($payment->amount) }}</span>
                    @if ($payment->refunds->isNotEmpty())
                        @php($refAmt = $payment->refunds->where('status', 'completed')->sum('amount'))
                        @if ($refAmt > 0)
                            <span class="inline-flex items-center text-[10px] text-rose-800 bg-rose-50 border border-rose-300 px-1.5 py-0.5 rounded font-bold mt-1 whitespace-nowrap">Refund: -{{ rupiah($refAmt) }}</span>
                        @endif
                    @endif
                </td>
                <td class="py-3.5 px-4 text-center align-middle whitespace-nowrap">
                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider whitespace-nowrap {{ $payment->badgeClass() }} border shadow-xs">
                        {{ $payment->statusLabel() }}
                    </span>
                    @if ($payment->proof_path)
                        <a href="{{ $payment->proofUrl() }}" target="_blank" class="block text-[11px] text-amber-700 hover:text-amber-900 font-bold underline mt-1.5 whitespace-nowrap">
                            Lihat Struk ↗
                        </a>
                    @endif
                </td>
                <td class="py-3.5 px-4 text-right whitespace-nowrap align-middle">
                    @if ($payment->status === \App\Models\Payment::STATUS_PENDING && $payment->channel === \App\Models\Payment::CHANNEL_MANUAL_TRANSFER)
                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="inline">
                            @csrf
                            <button class="btn btn-success px-2.5 py-1.5 text-xs font-bold text-white shadow-sm whitespace-nowrap">Verifikasi</button>
                        </form>
                        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="inline" data-confirm="Tolak bukti transfer ini?">
                            @csrf
                            <button class="btn btn-danger px-2.5 py-1.5 text-xs font-bold text-white shadow-sm whitespace-nowrap">Tolak</button>
                        </form>
                    @elseif ($payment->isSuccessful() && $payment->status !== \App\Models\Payment::STATUS_REFUNDED)
                        <button type="button" onclick="openRefundModal({{ $payment->id }}, '{{ $payment->reference_id ?: $payment->booking->code }}', {{ $payment->refundableAmount() }})" class="btn btn-outline text-xs px-2.5 py-1.5 text-rose-700 border-rose-300 hover:bg-rose-50 font-bold shadow-sm whitespace-nowrap">
                            Refund
                        </button>
                    @else
                        <span class="text-slate-400 text-xs font-bold">-</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="py-12 text-center text-slate-500 bg-slate-50/50">
                    <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                        <x-icon name="search" class="w-6 h-6" />
                    </div>
                    <p class="font-bold text-slate-700 text-sm">Tidak Ada Transaksi Ditemukan</p>
                    <p class="text-xs text-slate-500 mt-0.5">Coba ubah kata kunci atau bersihkan kriteria filter pencarian.</p>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $payments->links() }}</div>

{{-- Modal Refund Form (Berlatar Putih Bersih & Tulisan Hitam Pekat) --}}
<div id="refundModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-md w-full p-6 bg-white shadow-2xl rounded-xl border border-slate-200 relative">
        <h3 class="text-lg font-black text-slate-900 uppercase tracking-wide">Proses Refund Pembayaran</h3>
        <p class="text-xs text-slate-600 mt-1 font-medium" id="refundModalSubtitle">
            Mengembalikan dana pelanggan untuk transaksi pembayaran.
        </p>

        <form id="refundForm" method="POST" action="" class="mt-4 space-y-4 text-xs">
            @csrf
            <div>
                <label class="label text-slate-700 font-extrabold uppercase text-[11px] block mb-1">Jumlah Refund (Rp)</label>
                <input type="number" id="refundAmountInput" name="amount" min="1000" max="1000000000" step="1" required class="input text-xs font-black text-slate-900 bg-white border border-slate-300 rounded px-3 py-2 w-full">
                <p class="text-[11px] text-slate-500 mt-1">Maksimal pengembalian: <span id="maxRefundText" class="font-bold text-slate-800"></span></p>
            </div>
            <div>
                <label class="label text-slate-700 font-extrabold uppercase text-[11px] block mb-1">Alasan Refund</label>
                <textarea name="reason" rows="2" required placeholder="Contoh: Pembatalan sewa atas permintaan pelanggan, kelebihan transfer..." class="input text-xs text-slate-900 bg-white border border-slate-300 rounded px-3 py-2 w-full font-medium" maxlength="500"></textarea>
            </div>
            <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeRefundModal()" class="btn btn-outline text-xs py-2 px-3.5 font-bold uppercase tracking-wider text-slate-700 border-slate-300 hover:bg-slate-100">Batal</button>
                <button type="submit" class="btn btn-danger text-xs py-2 px-4 font-black uppercase tracking-wider text-white bg-rose-600 hover:bg-rose-700 shadow-sm">Konfirmasi Refund</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRefundModal(paymentId, ref, maxAmount) {
    const modal = document.getElementById('refundModal');
    const form = document.getElementById('refundForm');
    const subtitle = document.getElementById('refundModalSubtitle');
    const input = document.getElementById('refundAmountInput');
    const maxText = document.getElementById('maxRefundText');

    form.action = `/admin/pembayaran/${paymentId}/refund`;
    subtitle.textContent = `Transaksi: ${ref}`;
    input.max = maxAmount;
    input.value = maxAmount;
    maxText.textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(maxAmount);

    modal.classList.remove('hidden');
}

function closeRefundModal() {
    document.getElementById('refundModal').classList.add('hidden');
}
</script>
@endsection
