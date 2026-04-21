<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_sales')) {
                $table->boolean('is_sales')->default(0)->after('is_mediator');
            }
            if (! Schema::hasColumn('users', 'sales_partner_type')) {
                $table->string('sales_partner_type', 20)->nullable()->after('is_sales');
            }
            if (! Schema::hasColumn('users', 'partner_id')) {
                $table->unsignedBigInteger('partner_id')->nullable()->after('sales_partner_type');
                $table->index('partner_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'partner_id')) {
                $table->dropIndex(['partner_id']);
                $table->dropColumn('partner_id');
            }
            if (Schema::hasColumn('users', 'sales_partner_type')) {
                $table->dropColumn('sales_partner_type');
            }
            if (Schema::hasColumn('users', 'is_sales')) {
                $table->dropColumn('is_sales');
            }
        });
    }
};
