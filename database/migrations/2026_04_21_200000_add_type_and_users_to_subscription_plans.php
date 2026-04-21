<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Drop plan_price first so we can recreate it with larger precision
            // Or change it
            $table->decimal('plan_price', 15, 2)->change();
            $table->string('plan_type')->default('showroom_baru')->after('plan_price');
            $table->integer('max_user')->default(1)->after('max_car');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->decimal('plan_price', 8, 2)->change();
            $table->dropColumn('plan_type');
            $table->dropColumn('max_user');
        });
    }
};
