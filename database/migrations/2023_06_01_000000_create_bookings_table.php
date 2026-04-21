<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings')) {
            return;
        }

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('car_id');
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->nullable();
            $table->decimal('platform_amount', 12, 2)->nullable();
            $table->unsignedBigInteger('pickup_location')->nullable();
            $table->unsignedBigInteger('return_location')->nullable();
            $table->date('pickup_date')->nullable();
            $table->string('pickup_time')->nullable();
            $table->date('return_date')->nullable();
            $table->string('return_time')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable();
            $table->text('transaction')->nullable();
            $table->text('booking_note')->nullable();
            $table->integer('status')->default(0);
            $table->enum('application_type', ['rental', 'leasing'])->default('rental');
            $table->decimal('down_payment', 10, 2)->nullable();
            $table->decimal('installment_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('mediator_id')->nullable();
            $table->unsignedBigInteger('marketing_id')->nullable();
            $table->unsignedBigInteger('showroom_id')->nullable();
            $table->enum('leasing_status', ['pending', 'review', 'approved', 'rejected', 'appealed'])->nullable();
            $table->text('leasing_notes')->nullable();
            $table->json('application_documents')->nullable();
            $table->timestamp('pooled_at')->nullable();
            $table->timestamp('appealed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
