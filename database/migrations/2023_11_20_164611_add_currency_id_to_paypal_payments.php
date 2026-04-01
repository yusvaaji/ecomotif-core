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
        if (Schema::hasTable('paypal_payments') && !Schema::hasColumn('paypal_payments', 'currency_id')) {
            Schema::table('paypal_payments', function (Blueprint $table) {
                $table->integer('currency_id')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('paypal_payments') && Schema::hasColumn('paypal_payments', 'currency_id')) {
            Schema::table('paypal_payments', function (Blueprint $table) {
                $table->dropColumn('currency_id');
            });
        }
    }
};
