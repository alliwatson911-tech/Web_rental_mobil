<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('car_id')->constrained()->restrictOnDelete();
            $table->foreignId('pickup_location_id')->constrained('locations')->restrictOnDelete();
            $table->string('delivery_address')->nullable();

            // Data pelanggan disalin ke pesanan
            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->string('customer_email')->nullable();
            $table->string('customer_address')->nullable();

            // Rentang sewa [pickup_date, return_date)
            $table->date('pickup_date');
            $table->date('return_date');
            $table->unsignedSmallInteger('days');

            // Harga disalin saat pesanan dibuat (snapshot)
            $table->unsignedInteger('daily_price');
            $table->unsignedInteger('base_total');
            $table->unsignedInteger('addons_total')->default(0);
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('total');

            $table->string('status', 20)->default('pending'); // pending|confirmed|active|completed|cancelled
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['car_id', 'status', 'pickup_date', 'return_date']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
