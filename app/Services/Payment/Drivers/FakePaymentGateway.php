<?php

namespace App\Services\Payment\Drivers;

use App\Models\Payment;
use App\Services\Payment\DTOs\ChargeRequest;
use App\Services\Payment\DTOs\ChargeResult;
use App\Services\Payment\DTOs\RefundResult;
use App\Services\Payment\DTOs\WebhookResult;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGatewayInterface
{
    protected ?string $simulatedStatus = null;

    public function getName(): string
    {
        return 'fake';
    }

    /** Membantu testing untuk mensimulasikan status gateway tertentu */
    public function setSimulatedStatus(?string $status): self
    {
        $this->simulatedStatus = $status;
        return $this;
    }

    public function createCharge(ChargeRequest $request): ChargeResult
    {
        $externalId = 'FAKE-INV-' . strtoupper(Str::random(10));
        $checkoutUrl = route('bookings.pay.checkout', ['booking' => $request->booking->code, 'ref' => $request->referenceId]);

        $qrCodeUrl = null;
        $vaNumber = null;

        if ($request->channel === 'qris') {
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode("PADAIDI-QRIS-{$request->referenceId}-{$request->amount}");
        } elseif (str_starts_with($request->channel, 'va_')) {
            $bankPrefix = match ($request->channel) {
                'va_bca' => '8801',
                'va_bni' => '8802',
                'va_bri' => '8803',
                'va_mandiri' => '8804',
                default => '8805',
            };
            $cleanPhone = preg_replace('/\D+/', '', $request->customerPhone);
            $vaNumber = $bankPrefix . substr($cleanPhone, -8);
        }

        return new ChargeResult(
            success: true,
            referenceId: $request->referenceId,
            externalId: $externalId,
            checkoutUrl: $checkoutUrl,
            qrCodeUrl: $qrCodeUrl,
            vaNumber: $vaNumber,
            rawResponse: [
                'status' => 'PENDING',
                'external_id' => $externalId,
                'amount' => $request->amount,
                'channel' => $request->channel,
                'simulated' => true,
            ],
            errorMessage: null
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        // Untuk driver fake, validasi token jika header disetel, atau terima jika testing
        $token = $request->header('x-callback-token') ?? $request->header('X-CALLBACK-TOKEN');
        $expectedToken = config('payment.xendit.webhook_token', 'padaidi-xendit-webhook-token');

        if ($token !== null && $token !== '' && $token !== $expectedToken) {
            return false;
        }

        return true;
    }

    public function parseWebhook(Request $request): WebhookResult
    {
        $payload = $request->all();
        $eventId = $payload['event_id'] ?? $payload['id'] ?? ('FAKE-EVT-' . md5(json_encode($payload)));
        $statusRaw = strtoupper($payload['status'] ?? 'PAID');

        $status = match ($statusRaw) {
            'PAID', 'SETTLED', 'SUCCESS' => Payment::STATUS_PAID,
            'EXPIRED' => Payment::STATUS_EXPIRED,
            'FAILED' => Payment::STATUS_FAILED,
            default => Payment::STATUS_PENDING,
        };

        return new WebhookResult(
            valid: true,
            eventId: $eventId,
            eventType: $payload['event'] ?? 'payment.status_updated',
            referenceId: $payload['external_id'] ?? $payload['reference_id'] ?? null,
            externalId: $payload['id'] ?? $payload['payment_id'] ?? null,
            status: $status,
            paidAmount: isset($payload['amount']) ? (int) $payload['amount'] : null,
            paidAt: now(),
            rawPayload: $payload,
            errorMessage: null
        );
    }

    public function getPaymentStatus(string $externalId): array
    {
        $status = $this->simulatedStatus ?? 'PAID';

        return [
            'status' => $status,
            'external_id' => $externalId,
            'is_paid' => $status === 'PAID',
            'is_expired' => $status === 'EXPIRED',
        ];
    }

    public function refund(Payment $payment, int $amount, string $reason): RefundResult
    {
        $refundId = 'FAKE-REF-' . strtoupper(Str::random(10));

        return new RefundResult(
            success: true,
            refundId: $refundId,
            amount: $amount,
            status: 'completed',
            rawResponse: [
                'refund_id' => $refundId,
                'amount' => $amount,
                'reason' => $reason,
                'simulated' => true,
            ],
            errorMessage: null
        );
    }
}

