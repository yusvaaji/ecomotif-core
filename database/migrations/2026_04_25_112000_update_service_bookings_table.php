<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->json('service_ids')->nullable()->after('garage_service_id');
            $table->string('location_benchmark')->nullable()->after('customer_address');
            $table->unsignedBigInteger('garage_service_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropColumn('service_ids');
            $table->dropColumn('location_benchmark');
            $table->unsignedBigInteger('garage_service_id')->nullable(false)->change();
        });
    }
};
