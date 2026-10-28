<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('membership_tier_id')->constrained('membership_tiers');
            $table->foreignId('manual_tier_id')->nullable()->constrained('membership_tiers')->nullOnDelete();
            $table->text('manual_tier_notes')->nullable();
            $table->timestamp('manual_tier_updated_at')->nullable();
            $table->foreignId('manual_tier_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('total_completed_rentals')->default(0);
            $table->unsignedBigInteger('total_spend')->default(0);
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('phone_verified_at')->nullable();
            $table->text('address')->nullable();
            $table->string('nik', 30)->nullable();
            $table->string('sim_number', 30)->nullable();
            $table->string('kyc_status', 20)->default('unverified');
            $table->text('kyc_notes')->nullable();
            $table->timestamp('kyc_verified_at')->nullable();
            $table->foreignId('kyc_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};
