<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Services\Payment\DTOs\ChargeRequest;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager,
        protected BookingService $bookingService
    ) {}

    /**
     * Membuat tagihan transaksi pembayaran baru dari pesanan.
     * Nominal SELALU dihitung ulang secara aman di server.
     */
    public function createPayment(
        Booking $booking,
        string $paymentType = Payment::TYPE_FULL,
        string $channel = 'qris'
    ): Payment {
        $remaining = $booking->remainingAmount();

        if ($remaining <= 0) {
            throw new InvalidArgumentException('Pesanan ini sudah lunas, tidak memerlukan pembayaran lagi.');
        }

        // Hitung nominal tagihan di server
        $amount = match ($paymentType) {
            Payment::TYPE_DP => min($remaining, (int) round($booking->grandTotal() * (config('payment.dp_percentage', 30) / 100))),
            default => $remaining,
        };

        // Buat nomor referensi unik
        $referenceId = 'PDD-PAY-' . $booking->code . '-' . time() . '-' . rand(100, 999);
        $expiresAt = now()->addHours(config('payment.expiry_hours', 24));
        $description = "Pembayaran Sewa Mobil Padaidi ({$booking->code}) - {$booking->car?->brand} {$booking->car?->model}";

        $chargeRequest = new ChargeRequest(
            booking: $booking,
            referenceId: $referenceId,
            amount: $amount,
            paymentType: $paymentType,
            channel: $channel,
            customerName: $booking->customer_name,
            customerPhone: $booking->customer_phone,
            customerEmail: $booking->customer_email,
            description: $description,
            expiresAt: $expiresAt,
        );

        $gateway = $this->gatewayManager->driver();
        $chargeResult = $gateway->createCharge($chargeRequest);

        if (! $chargeResult->success) {
            throw new \RuntimeException($chargeResult->errorMessage ?? 'Gagal membuat tagihan pembayaran ke payment gateway.');
        }

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'reference_id' => $referenceId,
            'external_id' => $chargeResult->externalId,
            'payment_type' => $paymentType,
            'amount' => $amount,
            'method' => 'gateway',
            'channel' => $channel,
            'gateway_name' => $gateway->getName(),
            'checkout_url' => $chargeResult->checkoutUrl,
            'qr_code_url' => $chargeResult->qrCodeUrl,
            'va_number' => $chargeResult->vaNumber,
            'status' => Payment::STATUS_PENDING,
            'expires_at' => $expiresAt,
            'raw_response' => $chargeResult->rawResponse,
        ]);

        return $payment;
    }

    /**
     * Memproses penerimaan notifikasi webhook yang aman, idempoten, dan mencatat peristiwa mentah.
     */
    public function handleWebhook(Request $request): array
    {
        $gateway = $this->gatewayManager->driver();

        // 1. Verifikasi tanda tangan / token webhook
        if (! $gateway->verifyWebhook($request)) {
            Log::warning('Webhook signature verification failed', [
                'headers' => $request->headers->all(),
                'ip' => $request->ip(),
            ]);

            return [
                'success' => false,
                'status' => 'unauthorized',
                'message' => 'Tanda tangan atau token webhook tidak sah.',
            ];
        }

        // 2. Urai payload
        $result = $gateway->parseWebhook($request);

        // 3. Cek Idempotensi melalui webhook_events
        $existingEvent = WebhookEvent::where('event_id', $result->eventId)->first();
        if ($existingEvent && $existingEvent->status === WebhookEvent::STATUS_PROCESSED) {
            return [
                'success' => true,
                'status' => 'already_processed',
                'message' => 'Peristiwa webhook sudah pernah diproses sebelumnya (idempoten).',
            ];
        }

        $event = WebhookEvent::create([
            'gateway' => $gateway->getName(),
            'event_id' => $result->eventId,
            'event_type' => $result->eventType,
            'payload' => $result->rawPayload,
            'status' => WebhookEvent::STATUS_PENDING,
        ]);

        // 4. Cari transaksi pembayaran
        $payment = Payment::where(function ($q) use ($result) {
            if ($result->referenceId) {
                $q->where('reference_id', $result->referenceId);
            }
            if ($result->externalId) {
                $q->orWhere('external_id', $result->externalId);
            }
        })->first();

        if (! $payment) {
            $event->update([
                'status' => WebhookEvent::STATUS_IGNORED,
                'error_message' => 'Transaksi pembayaran tidak ditemukan di basis data lokal.',
            ]);

            return [
                'success' => true,
                'status' => 'ignored',
                'message' => 'Pembayaran tidak ditemukan.',
            ];
        }

        // 5. Perbarui status pembayaran secara atomik
        DB::transaction(function () use ($payment, $result, $event) {
            if ($result->status === Payment::STATUS_PAID) {
                $payment->update([
                    'status' => Payment::STATUS_PAID,
                    'paid_at' => $result->paidAt ?? now(),
                    'raw_response' => array_merge($payment->raw_response ?? [], $result->rawPayload),
                ]);

                // Jika pesanan masih Pending, otomatis konfirmasi pesanan saat DP / lunas terbayar
                $booking = $payment->booking;
                if ($booking && $booking->status === BookingStatus::Pending) {
                    $this->bookingService->transition($booking, BookingStatus::Confirmed);
                }
            } elseif ($result->status === Payment::STATUS_EXPIRED) {
                $payment->update([
                    'status' => Payment::STATUS_EXPIRED,
                    'failure_reason' => 'Tagihan telah kedaluwarsa melewati batas waktu.',
                ]);
            } elseif ($result->status === Payment::STATUS_FAILED) {
                $payment->update([
                    'status' => Payment::STATUS_FAILED,
                    'failure_reason' => $result->errorMessage ?? 'Pembayaran gagal ditagihkan.',
                ]);
            }

            $event->update(['status' => WebhookEvent::STATUS_PROCESSED]);
        });

        return [
            'success' => true,
            'status' => 'processed',
            'payment_id' => $payment->id,
            'payment_status' => $payment->status,
        ];
    }

    /**
     * Memeriksa dan memperbarui transaksi pending yang sudah melewati batas waktu kedaluwarsa.
     */
    public function expirePendingPayments(): int
    {
        $expiredCount = 0;
        $pendingPayments = Payment::where('status', Payment::STATUS_PENDING)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($pendingPayments as $payment) {
            $payment->update([
                'status' => Payment::STATUS_EXPIRED,
                'failure_reason' => 'Batas waktu pembayaran telah habis (otomatis oleh sistem scheduler).',
            ]);
            $expiredCount++;
        }

        return $expiredCount;
    }

    /**
     * Rekonsiliasi: Memeriksa langsung status transaksi pending ke payment gateway.
     */
    public function reconcilePayments(): int
    {
        $gateway = $this->gatewayManager->driver();
        $updatedCount = 0;

        $pendingPayments = Payment::where('status', Payment::STATUS_PENDING)
            ->whereNotNull('external_id')
            ->where('gateway_name', '!=', 'manual')
            ->get();

        foreach ($pendingPayments as $payment) {
            try {
                $statusData = $gateway->getPaymentStatus($payment->external_id);

                if (! empty($statusData['is_paid'])) {
                    $payment->update([
                        'status' => Payment::STATUS_PAID,
                        'paid_at' => now(),
                    ]);

                    $booking = $payment->booking;
                    if ($booking && $booking->status === BookingStatus::Pending) {
                        $this->bookingService->transition($booking, BookingStatus::Confirmed);
                    }
                    $updatedCount++;
                } elseif (! empty($statusData['is_expired'])) {
                    $payment->update([
                        'status' => Payment::STATUS_EXPIRED,
                        'failure_reason' => 'Kedaluwarsa berdasarkan sinkronisasi status gateway.',
                    ]);
                    $updatedCount++;
                }
            } catch (\Throwable $e) {
                Log::error("Rekonsiliasi gagal untuk payment #{$payment->id}", ['error' => $e->getMessage()]);
            }
        }

        return $updatedCount;
    }

    /**
     * Memproses pengembalian dana (refund) transaksi sewa.
     */
    public function refundPayment(
        Payment $payment,
        int $amount,
        string $reason,
        ?User $admin = null
    ): PaymentRefund {
        if (! $payment->isSuccessful()) {
            throw new InvalidArgumentException('Hanya pembayaran yang telah berhasil/lunas yang dapat di-refund.');
        }

        $alreadyRefunded = (int) $payment->refunds()->where('status', PaymentRefund::STATUS_COMPLETED)->sum('amount');
        $maxRefundable = $payment->amount - $alreadyRefunded;

        if ($amount <= 0 || $amount > $maxRefundable) {
            throw new InvalidArgumentException('Nominal refund tidak valid atau melebihi saldo yang dapat dikembalikan (Maks: ' . rupiah($maxRefundable) . ').');
        }

        return DB::transaction(function () use ($payment, $amount, $reason, $admin, $alreadyRefunded) {
            $gateway = $this->gatewayManager->driver($payment->gateway_name);
            $refundResult = $gateway->refund($payment, $amount, $reason);

            if (! $refundResult->success) {
                throw new \RuntimeException($refundResult->errorMessage ?? 'Proses refund ke payment gateway gagal.');
            }

            $refund = PaymentRefund::create([
                'payment_id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'refunded_by' => $admin?->id,
                'amount' => $amount,
                'reason' => $reason,
                'status' => PaymentRefund::STATUS_COMPLETED,
                'notes' => 'Refund ID Gateway: ' . ($refundResult->refundId ?? '-'),
            ]);

            $totalRefunded = $alreadyRefunded + $amount;
            if ($totalRefunded >= $payment->amount) {
                $payment->update(['status' => Payment::STATUS_REFUNDED]);
            } else {
                $payment->update(['status' => Payment::STATUS_PARTIALLY_REFUNDED]);
            }

            return $refund;
        });
    }
}

