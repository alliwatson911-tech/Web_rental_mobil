<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('target_type', 30)->default('coupon'); // tier, coupon, first_order, period
            $table->string('type', 20)->default('percentage'); // percentage, nominal
            $table->unsignedInteger('value'); // nilai persentase (e.g. 10) atau nominal Rupiah (e.g. 50000)
            $table->unsignedInteger('max_discount')->nullable(); // maksimal potongan jika persentase
            $table->unsignedInteger('min_order_amount')->default(0); // minimal subtotal mobil
            $table->unsignedInteger('min_days')->default(1); // minimal hari sewa
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable(); // batas total kuota pemakaian
            $table->unsignedInteger('usage_limit_per_user')->nullable(); // batas pemakaian per pengguna
            $table->unsignedInteger('used_count')->default(0); // penghitung pemakaian
            $table->boolean('is_combinable')->default(true); // boleh digabung dengan diskon lain
            $table->boolean('is_active')->default(true);
            
            // Cakupan promo
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('car_id')->nullable()->constrained('cars')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('promotion_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_phone', 30)->nullable();
            $table->unsignedInteger('discount_amount');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_redemptions');
        Schema::dropIfExists('promotions');
    }
};
