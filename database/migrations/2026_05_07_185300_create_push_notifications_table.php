<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->foreignId('admin_id')->nullable();
            $table->string('status')->default('sent');
            $table->string('target_type')->default('all');
            $table->json('target_ids')->nullable();
            $table->integer('recipients')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('push_notifications');
    }
};
