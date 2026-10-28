<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;

class ReconcilePaymentsCommand extends Command
{
    protected $signature = 'payments:reconcile';
    protected $description = 'Melakukan sinkronisasi dan rekonsiliasi status pembayaran pending langsung ke gateway';

    public function handle(PaymentService $paymentService): int
    {
        $this->info('Menjalankan rekonsiliasi status transaksi ke payment gateway...');
        $count = $paymentService->reconcilePayments();
        $this->info("Rekonsiliasi selesai. Sebanyak {$count} transaksi berhasil disinkronkan.");

        return Command::SUCCESS;
    }
}
