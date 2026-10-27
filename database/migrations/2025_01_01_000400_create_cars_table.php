<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand', 100);
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('plate_number', 20)->unique();
            $table->string('transmission', 20)->default('manual'); // manual | automatic
            $table->unsignedTinyInteger('seats')->default(5);
            $table->string('fuel', 30)->default('Bensin');
            $table->unsignedInteger('daily_price'); // Rupiah per hari
            $table->string('status', 20)->default('available')->index(); // available | maintenance | inactive
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
