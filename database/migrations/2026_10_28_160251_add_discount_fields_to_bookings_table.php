<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('promo_code', 50)->nullable()->after('discount');
            $table->unsignedInteger('tier_discount')->default(0)->after('promo_code');
            $table->unsignedInteger('promo_discount')->default(0)->after('tier_discount');
            $table->json('discount_snapshot')->nullable()->after('promo_discount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['promo_code', 'tier_discount', 'promo_discount', 'discount_snapshot']);
        });
    }
};
