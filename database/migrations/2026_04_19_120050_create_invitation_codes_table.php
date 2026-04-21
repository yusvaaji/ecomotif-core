<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('max_uses')->default(1000);
            $table->unsignedInteger('uses_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_codes');
    }
};
