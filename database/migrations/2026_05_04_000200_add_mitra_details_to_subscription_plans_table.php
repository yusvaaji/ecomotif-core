<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('mitra_type')->nullable(); // Bengkel / Showroom
            $table->string('category')->nullable(); // Resmi / Umum OR Baru / Bekas
            $table->string('vehicle_type')->nullable(); // Mobil / Motor
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['mitra_type', 'category', 'vehicle_type']);
        });
    }
};
