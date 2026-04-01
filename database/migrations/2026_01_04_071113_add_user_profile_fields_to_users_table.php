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
                // Username
                if (!Schema::hasColumn('users', 'username')) {
                    $table->string('username')->nullable()->after('name');
                }
                
                // Phone
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone')->nullable()->after('username');
                }
                
                // Country
                if (!Schema::hasColumn('users', 'country')) {
                    $table->string('country')->nullable()->after('phone');
                }
                
                // Address
                if (!Schema::hasColumn('users', 'address')) {
                    $table->text('address')->nullable()->after('country');
                }
                
                // Designation
                if (!Schema::hasColumn('users', 'designation')) {
                    $table->string('designation')->nullable()->after('address');
                }
                
                // Image
                if (!Schema::hasColumn('users', 'image')) {
                    $table->string('image')->nullable()->after('designation');
                }
                
                // Status (enable/disable)
                if (!Schema::hasColumn('users', 'status')) {
                    $table->enum('status', ['enable', 'disable'])->default('enable')->after('image');
                }
                
                // Is Banned
                if (!Schema::hasColumn('users', 'is_banned')) {
                    $table->enum('is_banned', ['yes', 'no'])->default('no')->after('status');
                }
                
                // Is Influencer
                if (!Schema::hasColumn('users', 'is_influencer')) {
                    $table->boolean('is_influencer')->default(0)->after('is_banned');
                }
                
                // Is Dealer
                if (!Schema::hasColumn('users', 'is_dealer')) {
                    $table->boolean('is_dealer')->default(0)->after('is_influencer');
                }
                
                // About Me
                if (!Schema::hasColumn('users', 'about_me')) {
                    $table->text('about_me')->nullable()->after('is_dealer');
                }
                
                // Social Media Fields
                if (!Schema::hasColumn('users', 'facebook')) {
                    $table->string('facebook')->nullable()->after('about_me');
                }
                
                if (!Schema::hasColumn('users', 'linkedin')) {
                    $table->string('linkedin')->nullable()->after('facebook');
                }
                
                if (!Schema::hasColumn('users', 'twitter')) {
                    $table->string('twitter')->nullable()->after('linkedin');
                }
                
                if (!Schema::hasColumn('users', 'instagram')) {
                    $table->string('instagram')->nullable()->after('twitter');
                }
                
                // Working Hours
                if (!Schema::hasColumn('users', 'sunday')) {
                    $table->string('sunday')->nullable()->after('instagram');
                }
                
                if (!Schema::hasColumn('users', 'monday')) {
                    $table->string('monday')->nullable()->after('sunday');
                }
                
                if (!Schema::hasColumn('users', 'tuesday')) {
                    $table->string('tuesday')->nullable()->after('monday');
                }
                
                if (!Schema::hasColumn('users', 'wednesday')) {
                    $table->string('wednesday')->nullable()->after('tuesday');
                }
                
                if (!Schema::hasColumn('users', 'thursday')) {
                    $table->string('thursday')->nullable()->after('wednesday');
                }
                
                if (!Schema::hasColumn('users', 'friday')) {
                    $table->string('friday')->nullable()->after('thursday');
                }
                
                if (!Schema::hasColumn('users', 'saturday')) {
                    $table->string('saturday')->nullable()->after('friday');
                }
                
                // Google Map
                if (!Schema::hasColumn('users', 'google_map')) {
                    $table->text('google_map')->nullable()->after('saturday');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $columnsToDrop = [
                    'username', 'phone', 'country', 'address', 'designation', 'image',
                    'status', 'is_banned', 'is_influencer', 'is_dealer', 'about_me',
                    'facebook', 'linkedin', 'twitter', 'instagram',
                    'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday',
                    'google_map'
                ];
                
                foreach ($columnsToDrop as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
