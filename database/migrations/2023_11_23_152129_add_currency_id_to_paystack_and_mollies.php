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
        if (Schema::hasTable('paystack_and_mollies')) {
            Schema::table('paystack_and_mollies', function (Blueprint $table) {
                if (!Schema::hasColumn('paystack_and_mollies', 'paystack_currency_id')) {
                    $table->integer('paystack_currency_id')->default(0);
                }
                if (!Schema::hasColumn('paystack_and_mollies', 'mollie_currency_id')) {
                    $table->integer('mollie_currency_id')->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('paystack_and_mollies')) {
            Schema::table('paystack_and_mollies', function (Blueprint $table) {
                if (Schema::hasColumn('paystack_and_mollies', 'paystack_currency_id')) {
                    $table->dropColumn('paystack_currency_id');
                }
                if (Schema::hasColumn('paystack_and_mollies', 'mollie_currency_id')) {
                    $table->dropColumn('mollie_currency_id');
                }
            });
        }
    }
};
