<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Biaya perjalanan per tier jarak pada profil bengkel ────────────
        Schema::table('merchant_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('merchant_profiles', 'travel_fee_0_1km')) {
                // 0 – 1 km
                $table->unsignedInteger('travel_fee_0_1km')->default(0)->after('closing_hour');
            }
            if (! Schema::hasColumn('merchant_profiles', 'travel_fee_1_5km')) {
                // > 1 km – 5 km
                $table->unsignedInteger('travel_fee_1_5km')->default(0)->after('travel_fee_0_1km');
            }
            if (! Schema::hasColumn('merchant_profiles', 'travel_fee_5_10km')) {
                // > 5 km – 10 km
                $table->unsignedInteger('travel_fee_5_10km')->default(0)->after('travel_fee_1_5km');
            }
            if (! Schema::hasColumn('merchant_profiles', 'travel_fee_10km_plus')) {
                // > 10 km
                $table->unsignedInteger('travel_fee_10km_plus')->default(0)->after('travel_fee_5_10km');
            }
        });

        // ── Simpan biaya perjalanan & jarak aktual pada booking ────────────
        Schema::table('service_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('service_bookings', 'travel_fee')) {
                $table->decimal('travel_fee', 12, 2)->default(0)->after('total_price');
            }
            if (! Schema::hasColumn('service_bookings', 'travel_distance_km')) {
                $table->decimal('travel_distance_km', 8, 2)->nullable()->after('travel_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('merchant_profiles', function (Blueprint $table) {
            foreach (['travel_fee_0_1km', 'travel_fee_1_5km', 'travel_fee_5_10km', 'travel_fee_10km_plus'] as $col) {
                if (Schema::hasColumn('merchant_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('service_bookings', function (Blueprint $table) {
            foreach (['travel_fee', 'travel_distance_km'] as $col) {
                if (Schema::hasColumn('service_bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
