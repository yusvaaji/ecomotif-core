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
        Schema::table('home_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('home_pages', 'hero_video')) {
                $table->string('hero_video')->nullable()->after('video_id');
            }
        });
        
        Schema::table('home_page_translations', function (Blueprint $table) {
            if (!Schema::hasColumn('home_page_translations', 'home1_intro_description')) {
                $table->text('home1_intro_description')->nullable()->after('home1_intro_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_pages', function (Blueprint $table) {
            if (Schema::hasColumn('home_pages', 'hero_video')) {
                $table->dropColumn('hero_video');
            }
        });
        
        Schema::table('home_page_translations', function (Blueprint $table) {
            if (Schema::hasColumn('home_page_translations', 'home1_intro_description')) {
                $table->dropColumn('home1_intro_description');
            }
        });
    }
};
