<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('push_notifications')) {
            Schema::table('push_notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('push_notifications', 'target_type')) {
                    $table->string('target_type')->default('all')->after('status');
                }
                if (!Schema::hasColumn('push_notifications', 'target_ids')) {
                    $table->json('target_ids')->nullable()->after('target_type');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('push_notifications')) {
            Schema::table('push_notifications', function (Blueprint $table) {
                if (Schema::hasColumn('push_notifications', 'target_type')) {
                    $table->dropColumn('target_type');
                }
                if (Schema::hasColumn('push_notifications', 'target_ids')) {
                    $table->dropColumn('target_ids');
                }
            });
        }
    }
};
