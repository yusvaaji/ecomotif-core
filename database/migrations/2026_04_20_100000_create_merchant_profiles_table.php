<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const USER_MERCHANT_COLUMNS = [
        'showroom_category',
        'showroom_type',
        'garage_category',
        'pic_name',
        'pic_email',
        'pic_phone',
        'invitation_code',
        'payment_proof_path',
        'business_photo_path',
        'terms_accepted_at',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('merchant_profiles')) {
            Schema::create('merchant_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('business_type', 20);
                $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
                $table->string('business_category', 120)->nullable();
                $table->string('showroom_type', 120)->nullable();
                $table->text('garage_services_description')->nullable();
                $table->string('pic_name')->nullable();
                $table->string('pic_email')->nullable();
                $table->string('pic_phone', 30)->nullable();
                $table->string('invitation_code', 128)->nullable();
                $table->string('payment_proof_path', 512)->nullable();
                $table->string('business_photo_path', 512)->nullable();
                $table->timestamp('terms_accepted_at')->nullable();
                $table->timestamps();
                $table->unique('user_id');
            });
        }

        $this->backfillFromUsers();

        Schema::table('users', function (Blueprint $table) {
            foreach (self::USER_MERCHANT_COLUMNS as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
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

        if (! Schema::hasTable('merchant_profiles')) {
            return;
        }

        $profiles = DB::table('merchant_profiles')->get();
        foreach ($profiles as $p) {
            $isGarage = $p->business_type === 'garage';
            $update = [
                'showroom_category' => ! $isGarage ? $p->business_category : null,
                'garage_category' => $isGarage ? $p->business_category : null,
                'showroom_type' => $p->showroom_type,
                'pic_name' => $p->pic_name,
                'pic_email' => $p->pic_email,
                'pic_phone' => $p->pic_phone,
                'invitation_code' => $p->invitation_code,
                'payment_proof_path' => $p->payment_proof_path,
                'business_photo_path' => $p->business_photo_path,
                'terms_accepted_at' => $p->terms_accepted_at,
            ];
            if ($isGarage) {
                $update['specialization'] = $p->garage_services_description;
            }
            DB::table('users')->where('id', $p->user_id)->update($update);
        }

        Schema::dropIfExists('merchant_profiles');
    }

    private function backfillFromUsers(): void
    {
        $rows = DB::table('users')
            ->where(function ($q) {
                $q->where('is_dealer', 1)->orWhere('is_garage', 1);
            })
            ->get();

        foreach ($rows as $u) {
            if (DB::table('merchant_profiles')->where('user_id', $u->id)->exists()) {
                continue;
            }

            $a = json_decode(json_encode($u), true);
            $isGarage = (int) ($a['is_garage'] ?? 0) === 1;
            $businessType = $isGarage ? 'garage' : 'showroom';

            DB::table('merchant_profiles')->insert([
                'user_id' => $a['id'],
                'business_type' => $businessType,
                'subscription_plan_id' => null,
                'business_category' => $isGarage ? ($a['garage_category'] ?? null) : ($a['showroom_category'] ?? null),
                'showroom_type' => $a['showroom_type'] ?? null,
                'garage_services_description' => $isGarage ? ($a['specialization'] ?? null) : null,
                'pic_name' => $a['pic_name'] ?? null,
                'pic_email' => $a['pic_email'] ?? null,
                'pic_phone' => $a['pic_phone'] ?? null,
                'invitation_code' => $a['invitation_code'] ?? null,
                'payment_proof_path' => $a['payment_proof_path'] ?? null,
                'business_photo_path' => $a['business_photo_path'] ?? null,
                'terms_accepted_at' => $a['terms_accepted_at'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
