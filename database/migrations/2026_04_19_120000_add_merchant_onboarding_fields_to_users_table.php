<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'showroom_category')) {
                $table->string('showroom_category')->nullable();
            }
            if (! Schema::hasColumn('users', 'showroom_type')) {
                $table->string('showroom_type')->nullable();
            }
            if (! Schema::hasColumn('users', 'garage_category')) {
                $table->string('garage_category')->nullable();
            }
            if (! Schema::hasColumn('users', 'pic_name')) {
                $table->string('pic_name')->nullable();
            }
            if (! Schema::hasColumn('users', 'pic_email')) {
                $table->string('pic_email')->nullable();
            }
            if (! Schema::hasColumn('users', 'pic_phone')) {
                $table->string('pic_phone')->nullable();
            }
            if (! Schema::hasColumn('users', 'invitation_code')) {
                $table->string('invitation_code', 128)->nullable();
            }
            if (! Schema::hasColumn('users', 'payment_proof_path')) {
                $table->string('payment_proof_path', 512)->nullable();
            }
            if (! Schema::hasColumn('users', 'business_photo_path')) {
                $table->string('business_photo_path', 512)->nullable();
            }
            if (! Schema::hasColumn('users', 'terms_accepted_at')) {
                $table->timestamp('terms_accepted_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach ([
                'showroom_category', 'showroom_type', 'garage_category', 'pic_name', 'pic_email',
                'pic_phone', 'invitation_code', 'payment_proof_path', 'business_photo_path', 'terms_accepted_at',
            ] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
