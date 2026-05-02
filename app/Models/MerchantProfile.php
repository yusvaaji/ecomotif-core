<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Subscription\Entities\SubscriptionPlan;

class MerchantProfile extends Model
{
    public const BUSINESS_SHOWROOM = 'showroom';

    public const BUSINESS_GARAGE = 'garage';

    protected $fillable = [
        'user_id',
        'business_type',
        'subscription_plan_id',
        'business_category',
        'showroom_type',
        'garage_services_description',
        'served_brands',
        'pic_name',
        'pic_email',
        'pic_phone',
        'invitation_code',
        'payment_proof_path',
        'business_photo_path',
        'terms_accepted_at',
    ];

    protected $casts = [
        'terms_accepted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}
