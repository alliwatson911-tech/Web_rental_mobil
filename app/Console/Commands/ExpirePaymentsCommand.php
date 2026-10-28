<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;

class ExpirePaymentsCommand extends Command
{
    protected $signature = 'payments:expire';
    protected $description = 'Menandai transaksi pembayaran pending yang melewati batas waktu kedaluwarsa menjadi expired';

    public function handle(PaymentService $paymentService): int
    {
        $this->info('Memeriksa transaksi pending yang kedaluwarsa...');
        $count = $paymentService->expirePendingPayments();
        $this->info("Selesai. Sebanyak {$count} transaksi pending kedaluwarsa.");

        return Command::SUCCESS;
    }
}
