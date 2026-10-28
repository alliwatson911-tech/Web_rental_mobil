<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Handover & Inspection
            $table->unsignedInteger('start_odometer')->nullable()->after('picked_up_at');
            $table->unsignedInteger('end_odometer')->nullable()->after('completed_at');
            $table->string('fuel_out', 30)->nullable()->after('start_odometer');
            $table->string('fuel_in', 30)->nullable()->after('end_odometer');
            $table->text('handover_notes')->nullable()->after('fuel_out');
            $table->text('return_notes')->nullable()->after('fuel_in');

            // Driver assignment (jika opsi with driver)
            $table->string('driver_name')->nullable()->after('return_notes');
            $table->string('driver_phone', 30)->nullable()->after('driver_name');

            // Overtime & penalti keterlambatan
            $table->unsignedInteger('overtime_hours')->default(0)->after('driver_phone');
            $table->unsignedInteger('overtime_fee')->default(0)->after('overtime_hours');
            $table->unsignedInteger('extra_fee')->default(0)->after('overtime_fee');
            $table->string('extra_fee_notes')->nullable()->after('extra_fee');
        });

        Schema::table('customer_documents', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('path'); // pending, verified, rejected
            $table->text('notes')->nullable()->after('status');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->after('notes');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'start_odometer',
                'end_odometer',
                'fuel_out',
                'fuel_in',
                'handover_notes',
                'return_notes',
                'driver_name',
                'driver_phone',
                'overtime_hours',
                'overtime_fee',
                'extra_fee',
                'extra_fee_notes',
            ]);
        });

        Schema::table('customer_documents', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['status', 'notes', 'verified_by', 'verified_at']);
        });
    }
};
