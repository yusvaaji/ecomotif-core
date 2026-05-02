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
        Schema::table('merchant_profiles', function (Blueprint $table) {
            $table->text('served_brands')->nullable()->after('garage_services_description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('merchant_profiles', function (Blueprint $table) {
            $table->dropColumn('served_brands');
        });
    }
};
