<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Services\Payment\DTOs\ChargeRequest;
use App\Services\Payment\DTOs\ChargeResult;
use App\Services\Payment\DTOs\RefundResult;
use App\Services\Payment\DTOs\WebhookResult;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /** Nama unik driver gateway (misal: 'fake', 'xendit') */
    public function getName(): string;

    /** Membuat tagihan / transaksi baru di payment gateway */
    public function createCharge(ChargeRequest $request): ChargeResult;

    /** Memvalidasi keaslian tanda tangan atau token webhook */
    public function verifyWebhook(Request $request): bool;

    /** Mengurai payload webhook menjadi WebhookResult terstandarisasi */
    public function parseWebhook(Request $request): WebhookResult;

    /** Memeriksa status transaksi langsung ke server gateway (rekonsiliasi) */
    public function getPaymentStatus(string $externalId): array;

    /** Memproses pengembalian dana (refund) transaksi */
    public function refund(Payment $payment, int $amount, string $reason): RefundResult;
}

