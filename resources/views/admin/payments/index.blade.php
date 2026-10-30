@extends('layouts.admin')
@section('title', 'Kelola Transaksi & Pembayaran')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black uppercase text-white tracking-wide">Transaksi &amp; Pembayaran</h1>
        <p class="text-xs text-slate-400 mt-0.5">Monitoring pembayaran gateway (QRIS, VA Bank), mutasi transfer manual, dan kelola refund.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn btn-outline text-xs flex items-center gap-1.5 py-2 min-h-[44px]">
            <x-icon name="download" class="w-4 h-4 text-amber-400" />
            <span class="font-bold">Ekspor Data (CSV)</span>
        </a>
    </div>
</div>

{{-- Kartu Ringkasan Metrik --}}
<div class="grid gap-4 sm:grid-cols-3 mb-6">
    <div class="card p-5 bg-[#111827] border border-[#1e293b] border-l-4 border-l-emerald-500 shadow-md">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Diterima / Lunas</p>
        <p class="mt-1 text-2xl font-black text-emerald-400">{{ rupiah($totalPaid) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">Status Paid &amp; Verified</p>
    </div>
    <div class="card p-5 bg-[#111827] border border-[#1e293b] border-l-4 border-l-amber-500 shadow-md">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Menunggu Pembayaran</p>
        <p class="mt-1 text-2xl font-black text-[#F5A623]">{{ rupiah($totalPending) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">Status Pending / Tagihan Aktif</p>
    </div>
    <div class="card p-5 bg-[#111827] border border-[#1e293b] border-l-4 border-l-rose-500 shadow-md">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Refund / Pengembalian</p>
        <p class="mt-1 text-2xl font-black text-rose-400">{{ rupiah($totalRefunded) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">Dana yang telah dikembalikan</p>
    </div>
</div>

{{-- Filter & Pencarian --}}
<div class="card p-5 mb-6 bg-[#111827] border border-[#1e293b]">
    <form method="GET" action="{{ route('admin.payments.index') }}" class="grid gap-3 md:grid-cols-5 text-xs">
        <div>
            <label class="label text-xs">Cari Transaksi / Pesanan</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Ref ID, Kode, Nama..." class="input text-xs">
        </div>
        <div>
            <label class="label text-xs">Status Pembayaran</label>
            <select name="status" class="input text-xs">
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
        <div>
            <label class="label text-xs">Metode / Kanal</label>
            <select name="channel" class="input text-xs">
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
        <div>
            <label class="label text-xs">Tanggal Dari</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="input text-xs">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn btn-primary text-xs py-2 flex-1">Filter</button>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline text-xs py-2">Reset</a>
        </div>
    </form>
</div>

{{-- Tabel Pembayaran --}}
<div class="card overflow-x-auto bg-[#111827] border border-[#1e293b]">
    <table class="table-base text-xs">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Referensi / ID</th>
                <th>Pesanan</th>
                <th>Pelanggan</th>
                <th>Kanal / Tipe</th>
                <th>Nominal</th>
                <th>Status</th>
                <th class="text-right">Tindakan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#1e293b]">
        @forelse ($payments as $payment)
            <tr>
                <td class="whitespace-nowrap text-slate-400">
                    {{ tanggal($payment->created_at, 'd M Y H:i') }}
                    @if ($payment->paid_at)
                        <span class="block text-[11px] text-emerald-400 font-bold">Lunas: {{ tanggal($payment->paid_at, 'H:i') }}</span>
                    @endif
                </td>
                <td>
                    <span class="font-mono font-bold text-white">{{ $payment->reference_id ?: 'MANUAL-'.$payment->id }}</span>
                    @if ($payment->gateway_name)
                        <span class="block text-[10px] uppercase text-slate-400">{{ $payment->gateway_name }}</span>
                    @endif
                </td>
                <td>
                    <a class="font-mono font-bold text-[#F5A623] hover:underline" href="{{ route('admin.bookings.show', $payment->booking) }}">
                        {{ $payment->booking->code }}
                    </a>
                    <span class="block text-[11px] text-slate-400">{{ $payment->booking->car->name }}</span>
                </td>
                <td>
                    <span class="font-bold text-white">{{ $payment->booking->customer_name }}</span>
                    @if ($payment->sender_name && $payment->sender_name !== $payment->booking->customer_name)
                        <span class="block text-[10px] text-slate-400">Pengirim: {{ $payment->sender_name }}</span>
                    @endif
                </td>
                <td>
                    <span class="font-semibold text-white">{{ $payment->channelLabel() }}</span>
                    <span class="block text-[11px] text-slate-400">{{ $payment->paymentTypeLabel() }}</span>
                    @if ($payment->va_number)
                        <span class="block font-mono text-[10px] text-[#F5A623] font-bold">VA: {{ $payment->va_number }}</span>
                    @endif
                </td>
                <td class="whitespace-nowrap font-extrabold text-white">
                    {{ rupiah($payment->amount) }}
                    @if ($payment->refunds->isNotEmpty())
                        @php($refAmt = $payment->refunds->where('status', 'completed')->sum('amount'))
                        @if ($refAmt > 0)
                            <span class="block text-[10px] text-rose-400 font-bold">Refund: -{{ rupiah($refAmt) }}</span>
                        @endif
                    @endif
                </td>
                <td>
                    <span class="badge {{ $payment->badgeClass() }} text-[11px]">
                        {{ $payment->statusLabel() }}
                    </span>
                    @if ($payment->proof_path)
                        <a href="{{ $payment->proofUrl() }}" target="_blank" class="block text-[10px] text-amber-400 hover:text-amber-300 font-bold underline mt-0.5">
                            Lihat Struk
                        </a>
                    @endif
                </td>
                <td class="text-right whitespace-nowrap">
                    @if ($payment->status === \App\Models\Payment::STATUS_PENDING && $payment->channel === \App\Models\Payment::CHANNEL_MANUAL_TRANSFER)
                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="inline">
                            @csrf
                            <button class="btn btn-success px-2 py-1 text-xs">Verifikasi</button>
                        </form>
                        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="inline" data-confirm="Tolak bukti transfer ini?">
                            @csrf
                            <button class="btn btn-danger px-2 py-1 text-xs">Tolak</button>
                        </form>
                    @elseif ($payment->isSuccessful() && $payment->status !== \App\Models\Payment::STATUS_REFUNDED)
                        {{-- Tombol Buka Modal Refund --}}
                        <button type="button" onclick="openRefundModal({{ $payment->id }}, '{{ $payment->reference_id ?: $payment->booking->code }}', {{ $payment->refundableAmount() }})" class="btn btn-outline text-[11px] px-2 py-1 text-rose-600 border-rose-300 hover:bg-rose-50">
                            Refund
                        </button>
                    @else
                        <span class="text-slate-400 text-xs">-</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="py-8 text-center text-slate-500">
                    Tidak ada transaksi pembayaran yang cocok dengan kriteria filter.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $payments->links() }}</div>

{{-- Modal Refund Form --}}
<div id="refundModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-md w-full p-6 bg-white shadow-2xl relative">
        <h3 class="text-lg font-bold text-slate-900">Proses Refund Pembayaran</h3>
        <p class="text-xs text-slate-500 mt-1" id="refundModalSubtitle">
            Mengembalikan dana pelanggan untuk transaksi pembayaran.
        </p>

        <form id="refundForm" method="POST" action="" class="mt-4 space-y-4 text-xs">
            @csrf
            <div>
                <label class="label text-xs">Jumlah Refund (Rp)</label>
                <input type="number" id="refundAmountInput" name="amount" min="1000" step="1" required class="input text-xs font-bold text-slate-900">
                <p class="text-[10px] text-slate-400 mt-1">Maksimal pengembalian: <span id="maxRefundText" class="font-bold text-slate-700"></span></p>
            </div>
            <div>
                <label class="label text-xs">Alasan Refund</label>
                <textarea name="reason" rows="2" required placeholder="Contoh: Pembatalan sewa atas permintaan pelanggan, kelebihan transfer..." class="input text-xs"></textarea>
            </div>
            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeRefundModal()" class="btn btn-outline text-xs py-1.5">Batal</button>
                <button type="submit" class="btn btn-danger text-xs py-1.5">Konfirmasi Refund</button>
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
