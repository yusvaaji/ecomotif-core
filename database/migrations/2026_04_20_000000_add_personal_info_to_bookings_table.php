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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('consumer_name')->nullable()->after('application_type');
            $table->string('consumer_phone')->nullable()->after('consumer_name');
            $table->string('consumer_email')->nullable()->after('consumer_phone');
            $table->text('consumer_address')->nullable()->after('consumer_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['consumer_name', 'consumer_phone', 'consumer_email', 'consumer_address']);
        });
    }
};
