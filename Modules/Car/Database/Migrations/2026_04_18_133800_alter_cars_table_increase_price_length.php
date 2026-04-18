<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Using DB::statement is the safest way to alter columns without requiring doctrine/dbal
        DB::statement('ALTER TABLE cars MODIFY regular_price DECIMAL(15, 2) NOT NULL');
        DB::statement('ALTER TABLE cars MODIFY offer_price DECIMAL(15, 2) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE cars MODIFY regular_price DECIMAL(8, 2) NOT NULL');
        DB::statement('ALTER TABLE cars MODIFY offer_price DECIMAL(8, 2) NULL');
    }
};
