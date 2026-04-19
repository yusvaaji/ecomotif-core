<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_bookings')) {
            return;
        }

        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('service_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('service_bookings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_bookings')) {
            return;
        }

        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('service_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('service_bookings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
