<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecordPaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request): View
    {
        $query = Payment::with(['booking.car', 'verifier', 'refunds'])
            ->when($request->query('status'), function ($q, $status) {
                if ($status !== 'semua') {
                    $q->where('status', $status);
                }
            })
            ->when($request->query('channel'), function ($q, $channel) {
                $q->where('channel', $channel);
            })
            ->when($request->query('start_date'), fn ($q, $start) => $q->whereDate('created_at', '>=', $start))
            ->when($request->query('end_date'), fn ($q, $end) => $q->whereDate('created_at', '<=', $end))
            ->when($request->query('q'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('reference_id', 'like', "%{$search}%")
                        ->orWhere('external_id', 'like', "%{$search}%")
                        ->orWhere('sender_name', 'like', "%{$search}%")
                        ->orWhereHas('booking', fn ($b) => $b->where('code', 'like', "%{$search}%")->orWhere('customer_name', 'like', "%{$search}%"));
                });
            });

        $totalPaid = Payment::whereIn('status', [Payment::STATUS_PAID, Payment::STATUS_VERIFIED])->sum('amount');
        $totalPending = Payment::where('status', Payment::STATUS_PENDING)->sum('amount');
        $totalRefunded = PaymentRefund::where('status', PaymentRefund::STATUS_COMPLETED)->sum('amount');

        $payments = (clone $query)->latest()->paginate(20)->withQueryString();

        return view('admin.payments.index', compact('payments', 'totalPaid', 'totalPending', 'totalRefunded'));
    }

    /** Admin mencatat pembayaran manual (tunai/transfer) */
    public function store(RecordPaymentRequest $request, Booking $booking): RedirectResponse
    {
        $data = $request->validated();
        $booking->payments()->create([
            'amount' => $data['amount'],
            'method' => $data['method'],
            'channel' => $data['method'] === 'tunai' ? 'cash' : 'manual_transfer',
            'gateway_name' => 'manual',
            'sender_name' => $data['sender_name'] ?? null,
            'notes' => $data['notes'] ?? null,
            'paid_at' => $data['paid_at'],
            'proof_path' => $request->file('proof')?->store('payments', 'local'),
            'status' => Payment::STATUS_VERIFIED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Pembayaran manual berhasil dicatat.');
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $payment->update([
            'status' => Payment::STATUS_VERIFIED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        // Otomatis konfirmasi pesanan jika masih pending
        $booking = $payment->booking;
        if ($booking && $booking->status === \App\Enums\BookingStatus::Pending) {
            app(\App\Services\BookingService::class)->transition($booking, \App\Enums\BookingStatus::Confirmed);
        }

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $notes = $request->validate(['notes' => ['nullable', 'string', 'max:255']], [], ['notes' => 'catatan'])['notes'] ?? null;
        $payment->update([
            'status' => Payment::STATUS_REJECTED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'notes' => $notes ?: $payment->notes,
        ]);

        return back()->with('success', 'Pembayaran ditandai ditolak.');
    }

    /** Memproses Refund Transaksi */
    public function refund(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1000'],
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'amount.required' => 'Nominal pengembalian dana wajib diisi.',
            'reason.required' => 'Alasan pengembalian dana wajib dituliskan.',
        ]);

        try {
            $this->paymentService->refundPayment($payment, (int) $data['amount'], $data['reason'], $request->user());

            return back()->with('success', 'Pengembalian dana (refund) sebesar ' . rupiah($data['amount']) . ' berhasil diproses.');
        } catch (\Throwable $e) {
            return back()->withErrors(['refund' => $e->getMessage()]);
        }
    }

    /** Ekspor Laporan Pembayaran ke File CSV */
    public function export(Request $request): StreamedResponse
    {
        $payments = Payment::with(['booking', 'verifier'])
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan-pembayaran-padaidi-' . date('Ymd-His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID Transaksi',
                'Referensi',
                'ID Gateway',
                'Kode Booking',
                'Pelanggan',
                'Tipe Bayar',
                'Kanal',
                'Nominal (Rp)',
                'Status',
                'Waktu Bayar',
                'Diverifikasi Oleh',
            ]);

            foreach ($payments as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->reference_id ?? '-',
                    $p->external_id ?? '-',
                    $p->booking?->code ?? '-',
                    $p->booking?->customer_name ?? $p->sender_name ?? '-',
                    $p->paymentTypeLabel(),
                    $p->channelLabel(),
                    $p->amount,
                    $p->statusLabel(),
                    $p->paid_at ? $p->paid_at->format('Y-m-d H:i:s') : '-',
                    $p->verifier?->name ?? 'Sistem Gateway',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
