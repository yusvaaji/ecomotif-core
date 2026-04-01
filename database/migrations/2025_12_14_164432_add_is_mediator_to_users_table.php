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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Check dulu sebelum add untuk safety
                if (!Schema::hasColumn('users', 'is_mediator')) {
                    // Try to add after is_dealer if exists, otherwise add at end
                    if (Schema::hasColumn('users', 'is_dealer')) {
                        $table->boolean('is_mediator')->default(0)->after('is_dealer');
                    } else {
                        $table->boolean('is_mediator')->default(0);
                    }
                }
                
                if (!Schema::hasColumn('users', 'showroom_id')) {
                    if (Schema::hasColumn('users', 'is_mediator')) {
                        $table->integer('showroom_id')->nullable()->after('is_mediator');
                    } else {
                        $table->integer('showroom_id')->nullable();
                    }
                }
                
                if (!Schema::hasColumn('users', 'barcode')) {
                    if (Schema::hasColumn('users', 'showroom_id')) {
                        $table->string('barcode')->nullable()->after('showroom_id');
                    } else {
                        $table->string('barcode')->nullable();
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'barcode')) {
                $table->dropColumn('barcode');
            }
            
            if (Schema::hasColumn('users', 'showroom_id')) {
                $table->dropColumn('showroom_id');
            }
            
            if (Schema::hasColumn('users', 'is_mediator')) {
                $table->dropColumn('is_mediator');
            }
        });
    }
};
