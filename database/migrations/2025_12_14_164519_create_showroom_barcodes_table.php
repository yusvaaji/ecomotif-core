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
        if (!Schema::hasTable('showroom_barcodes')) {
            // Check if users table exists before creating foreign key
            if (Schema::hasTable('users')) {
                Schema::create('showroom_barcodes', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id'); // dealer/showroom user id
                    $table->string('barcode')->unique()->nullable();
                    $table->string('qr_code')->unique()->nullable();
                    $table->boolean('is_active')->default(1);
                    $table->timestamps();
                    
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            } else {
                // Create table without foreign key if users table doesn't exist
                Schema::create('showroom_barcodes', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id'); // dealer/showroom user id
                    $table->string('barcode')->unique()->nullable();
                    $table->string('qr_code')->unique()->nullable();
                    $table->boolean('is_active')->default(1);
                    $table->timestamps();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showroom_barcodes');
    }
};
