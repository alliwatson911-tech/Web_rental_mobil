<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jadwal mobil tidak tersedia (servis, perawatan, dipakai sendiri).
        // start_date s.d. end_date bersifat INKLUSIF (keduanya termasuk hari tidak tersedia).
        Schema::create('car_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason')->default('Servis');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['car_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_blocks');
    }
};
