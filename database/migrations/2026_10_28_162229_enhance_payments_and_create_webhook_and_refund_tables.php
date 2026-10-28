<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('reference_id', 100)->nullable()->unique()->after('booking_id');
            $table->string('external_id', 100)->nullable()->index()->after('reference_id');
            $table->string('payment_type', 30)->default('full')->after('amount'); // full, dp, settlement
            $table->string('channel', 50)->nullable()->after('method'); // qris, va_bca, va_bni, va_bri, va_mandiri, manual_transfer, cash
            $table->string('gateway_name', 50)->default('manual')->after('channel'); // fake, xendit, manual
            $table->text('checkout_url')->nullable()->after('gateway_name');
            $table->text('qr_code_url')->nullable()->after('checkout_url');
            $table->string('va_number', 100)->nullable()->after('qr_code_url');
            $table->timestamp('expires_at')->nullable()->index()->after('paid_at');
            $table->json('raw_response')->nullable()->after('notes');
            $table->text('failure_reason')->nullable()->after('raw_response');
        });

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 50);
            $table->string('event_id', 150)->unique();
            $table->string('event_type', 100)->nullable();
            $table->json('payload');
            $table->string('status', 30)->default('pending'); // pending, processed, ignored, failed
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('refunded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('reason');
            $table->string('status', 30)->default('completed'); // pending, completed, failed
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
        Schema::dropIfExists('webhook_events');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'reference_id',
                'external_id',
                'payment_type',
                'channel',
                'gateway_name',
                'checkout_url',
                'qr_code_url',
                'va_number',
                'expires_at',
                'raw_response',
                'failure_reason',
            ]);
        });
    }
};
