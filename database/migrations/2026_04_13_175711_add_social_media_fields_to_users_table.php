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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'instagram')) {
                $table->string('instagram')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'facebook')) {
                $table->string('facebook')->nullable()->after('instagram');
            }
            if (!Schema::hasColumn('users', 'twitter')) {
                $table->string('twitter')->nullable()->after('facebook');
            }
            if (!Schema::hasColumn('users', 'linkedin')) {
                $table->string('linkedin')->nullable()->after('twitter');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = ['instagram', 'facebook', 'twitter', 'linkedin'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
