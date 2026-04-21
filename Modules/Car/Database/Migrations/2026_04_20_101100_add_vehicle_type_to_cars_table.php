<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            if (! Schema::hasColumn('cars', 'vehicle_type')) {
                $table->string('vehicle_type', 20)->default('car')->after('condition');
            }
        });

        DB::table('cars')->update(['vehicle_type' => 'car']);

        DB::table('cars')
            ->where(function ($q) {
                $q->where('body_type', 'like', '%motor%')
                    ->orWhere('body_type', 'like', '%bike%')
                    ->orWhere('body_type', 'like', '%scooter%');
            })
            ->update(['vehicle_type' => 'motorcycle']);
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            if (Schema::hasColumn('cars', 'vehicle_type')) {
                $table->dropColumn('vehicle_type');
            }
        });
    }
};
