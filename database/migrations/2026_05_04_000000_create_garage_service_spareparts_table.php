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
        Schema::create('garage_service_spareparts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('garage_service_id');
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->timestamps();

            $table->foreign('garage_service_id')->references('id')->on('garage_services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garage_service_spareparts');
    }
};
