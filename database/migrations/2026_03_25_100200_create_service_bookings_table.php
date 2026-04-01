<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('garage_id');
            $table->unsignedBigInteger('garage_service_id');
            $table->enum('service_type', ['walk_in', 'home_service'])->default('walk_in');
            $table->date('booking_date');
            $table->string('booking_time');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address')->nullable();
            $table->string('vehicle_brand')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_year')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->decimal('total_price', 12, 2)->default(0);
            $table->text('garage_notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('garage_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('garage_service_id')->references('id')->on('garage_services')->onDelete('cascade');
            $table->index(['user_id', 'status']);
            $table->index(['garage_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
