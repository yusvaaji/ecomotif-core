<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garage_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('garage_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('duration')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('garage_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('garage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garage_services');
    }
};
