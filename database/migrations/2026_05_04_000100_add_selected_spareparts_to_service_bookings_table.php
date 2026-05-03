<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->json('selected_spareparts')->nullable()->after('service_ids');
        });
    }

    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropColumn('selected_spareparts');
        });
    }
};
