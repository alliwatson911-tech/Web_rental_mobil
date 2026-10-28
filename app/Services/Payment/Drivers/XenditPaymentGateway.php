<?php

namespace App\Services\Payment\Drivers;

use App\Models\Payment;
use App\Services\Payment\DTOs\ChargeRequest;
use App\Services\Payment\DTOs\ChargeResult;
use App\Services\Payment\DTOs\RefundResult;
use App\Services\Payment\DTOs\WebhookResult;
use App\Services\Payment\PaymentGatewayInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditPaymentGateway implements PaymentGatewayInterface
{
    protected string $secretKey;
    protected string $webhookToken;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = (string) config('payment.xendit.secret_key');
        $this->webhookToken = (string) config('payment.xendit.webhook_token');
        $this->baseUrl = (string) config('payment.xendit.base_url', 'https://api.xendit.co');
    }

    public function getName(): string
    {
        return 'xendit';
    }

    public function createCharge(ChargeRequest $request): ChargeResult
    {
        $paymentMethods = match ($request->channel) {
            'qris' => ['QRIS'],
            'va_bca' => ['BCA'],
            'va_bni' => ['BNI'],
            'va_bri' => ['BRI'],
            'va_mandiri' => ['MANDIRI'],
            'va_permata' => ['PERMATA'],
            default => ['QRIS', 'BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA'],
        };

        $payload = [
            'external_id' => $request->referenceId,
            'amount' => $request->amount,
            'description' => $request->description,
            'invoice_duration' => max(60, $request->expiresAt->diffInSeconds(now())),
            'customer' => [
                'given_names' => $request->customerName,
                'email' => $request->customerEmail ?: 'customer@padaidi.test',
                'mobile_number' => $request->customerPhone,
            ],
            'customer_notification_preference' => [
                'invoice_created' => ['whatsapp'],
                'invoice_reminder' => ['whatsapp'],
                'invoice_paid' => ['whatsapp'],
            ],
            'payment_methods' => $paymentMethods,
            'success_redirect_url' => route('bookings.show', $request->booking->code),
            'failure_redirect_url' => route('bookings.pay', $request->booking->code),
        ];

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->timeout(15)
                ->post("{$this->baseUrl}/v2/invoices", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return new ChargeResult(
                    success: true,
                    referenceId: $request->referenceId,
                    externalId: $data['id'] ?? null,
                    checkoutUrl: $data['invoice_url'] ?? null,
                    qrCodeUrl: $data['payment_details']['qr_code'] ?? null,
                    vaNumber: $data['payment_details']['account_number'] ?? null,
                    rawResponse: $data,
                    errorMessage: null
                );
            }

            Log::error('Xendit createCharge failed', ['response' => $response->body()]);

            return new ChargeResult(
                success: false,
                referenceId: $request->referenceId,
                errorMessage: $response->json('message') ?? 'Gagal membuat tagihan di Xendit.'
            );
        } catch (\Throwable $e) {
            Log::error('Xendit createCharge exception', ['error' => $e->getMessage()]);

            return new ChargeResult(
                success: false,
                referenceId: $request->referenceId,
                errorMessage: $e->getMessage()
            );
        }
    }

    public function verifyWebhook(Request $request): bool
    {
        $incomingToken = $request->header('x-callback-token') ?? $request->header('X-CALLBACK-TOKEN');

        if (empty($this->webhookToken)) {
            return true;
        }

        return hash_equals($this->webhookToken, (string) $incomingToken);
    }

    public function parseWebhook(Request $request): WebhookResult
    {
        $payload = $request->all();
        $eventId = $payload['id'] ?? ('XND-' . md5(json_encode($payload)));
        $rawStatus = strtoupper($payload['status'] ?? '');

        $status = match ($rawStatus) {
            'PAID', 'SETTLED' => Payment::STATUS_PAID,
            'EXPIRED' => Payment::STATUS_EXPIRED,
            'FAILED' => Payment::STATUS_FAILED,
            default => Payment::STATUS_PENDING,
        };

        $paidAt = isset($payload['paid_at']) ? Carbon::parse($payload['paid_at']) : now();

        return new WebhookResult(
            valid: true,
            eventId: $eventId,
            eventType: 'xendit.invoice.update',
            referenceId: $payload['external_id'] ?? null,
            externalId: $payload['id'] ?? null,
            status: $status,
            paidAmount: isset($payload['amount']) ? (int) $payload['amount'] : null,
            paidAt: $paidAt,
            rawPayload: $payload,
            errorMessage: null
        );
    }

    public function getPaymentStatus(string $externalId): array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->timeout(10)
                ->get("{$this->baseUrl}/v2/invoices/{$externalId}");

            if ($response->successful()) {
                $data = $response->json();
                $status = strtoupper($data['status'] ?? '');

                return [
                    'status' => $status,
                    'external_id' => $data['id'] ?? $externalId,
                    'is_paid' => in_array($status, ['PAID', 'SETTLED'], true),
                    'is_expired' => $status === 'EXPIRED',
                    'raw' => $data,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Xendit getPaymentStatus error', ['error' => $e->getMessage()]);
        }

        return ['status' => 'UNKNOWN', 'is_paid' => false, 'is_expired' => false];
    }

    public function refund(Payment $payment, int $amount, string $reason): RefundResult
    {
        // Xendit refund API integration
        try {
            $payload = [
                'invoice_id' => $payment->external_id,
                'amount' => $amount,
                'reason' => $reason,
            ];

            $response = Http::withBasicAuth($this->secretKey, '')
                ->timeout(15)
                ->post("{$this->baseUrl}/refunds", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return new RefundResult(
                    success: true,
                    refundId: $data['id'] ?? null,
                    amount: $amount,
                    status: 'completed',
                    rawResponse: $data
                );
            }

            return new RefundResult(
                success: false,
                amount: $amount,
                status: 'failed',
                errorMessage: $response->json('message') ?? 'Gagal memproses refund di Xendit.'
            );
        } catch (\Throwable $e) {
            return new RefundResult(
                success: false,
                amount: $amount,
                status: 'failed',
                errorMessage: $e->getMessage()
            );
        }
    }
}

