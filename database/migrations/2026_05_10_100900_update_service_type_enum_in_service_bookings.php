<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE service_bookings MODIFY COLUMN service_type ENUM('walk_in', 'home_service', 'emergency_service') DEFAULT 'walk_in'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // To safely revert, we would need to map 'emergency_service' back to 'home_service' first.
        DB::statement("UPDATE service_bookings SET service_type = 'home_service' WHERE service_type = 'emergency_service'");
        DB::statement("ALTER TABLE service_bookings MODIFY COLUMN service_type ENUM('walk_in', 'home_service') DEFAULT 'walk_in'");
    }
};
