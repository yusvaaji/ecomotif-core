<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchant_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('merchant_profiles', 'opening_hour')) {
                // e.g. "07:00:00"
                $table->time('opening_hour')->nullable()->after('garage_services_description');
            }
            if (! Schema::hasColumn('merchant_profiles', 'closing_hour')) {
                // e.g. "21:00:00"
                $table->time('closing_hour')->nullable()->after('opening_hour');
            }
        });
    }

    public function down(): void
    {
        Schema::table('merchant_profiles', function (Blueprint $table) {
            foreach (['opening_hour', 'closing_hour'] as $col) {
                if (Schema::hasColumn('merchant_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
