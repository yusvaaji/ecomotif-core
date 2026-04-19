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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->integer('user_id');
            $table->integer('supplier_id')->nullable();
            $table->integer('car_id');
            
            // Financials
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('platform_amount', 15, 2)->default(0);
            
            // Rental specifics
            $table->text('pickup_location')->nullable();
            $table->text('return_location')->nullable();
            $table->date('pickup_date')->nullable();
            $table->string('pickup_time')->nullable();
            $table->date('return_date')->nullable();
            $table->string('return_time')->nullable();
            $table->integer('duration')->nullable();
            
            // Payment info
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->text('transaction')->nullable();
            $table->text('booking_note')->nullable();
            
            // Booking Status
            $table->integer('status')->default(0); // 0=Pending, 1=Approved, etc.
            
            // Leasing / Purchasing specifics
            $table->string('application_type')->default('rental'); // rental, leasing
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->decimal('installment_amount', 15, 2)->default(0);
            $table->integer('mediator_id')->nullable();
            $table->integer('marketing_id')->nullable();
            $table->integer('showroom_id')->nullable();
            
            $table->string('leasing_status')->nullable();
            $table->text('leasing_notes')->nullable();
            $table->json('application_documents')->nullable(); // or text
            
            $table->timestamp('pooled_at')->nullable();
            $table->timestamp('appealed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
