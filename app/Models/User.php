<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Car\Entities\Car;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['total_car'];

    public function cars()
    {
        return $this->hasMany(Car::class, 'agent_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'agent_id');
    }

    public function getTotalCarAttribute()
    {
        return $this->cars->count();
    }

    /**
     * Check if user is mediator
     */
    public function isMediator()
    {
        return $this->is_mediator == 1;
    }

    /**
     * Check if user is marketing (linked to showroom)
     */
    public function isMarketing()
    {
        return $this->is_sales == 1 && $this->sales_partner_type === 'dealer';
    }

    /**
     * Check if user is mechanic (linked to garage)
     */
    public function isMechanic()
    {
        return $this->is_sales == 1 && $this->sales_partner_type === 'garage';
    }

    /**
     * Relationship: Applications created by this mediator
     */
    public function mediatorApplications()
    {
        return $this->hasMany(Booking::class, 'mediator_id');
    }

    /**
     * Relationship: Applications created by this marketing
     */
    public function marketingApplications()
    {
        return $this->hasMany(Booking::class, 'marketing_id');
    }

    /**
     * Relationship: Showroom (if user is linked to a showroom)
     */
    public function showroom()
    {
        return $this->belongsTo(User::class, 'partner_id')->where('is_dealer', 1);
    }

    /**
     * Relationship: Marketing users linked to this showroom
     */
    public function marketingUsers()
    {
        return $this->hasMany(User::class, 'partner_id')->where('is_sales', 1)->where('sales_partner_type', 'dealer');
    }

    /**
     * Relationship: Mediators linked to this showroom
     */
    public function mediators()
    {
        return $this->hasMany(User::class, 'partner_id')->where('is_mediator', 1);
    }

    public function isGarage()
    {
        return $this->is_garage == 1;
    }

    public function isSales(): bool
    {
        return $this->is_sales == 1;
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function garageServices()
    {
        return $this->hasMany(GarageService::class, 'garage_id');
    }

    public function serviceBookingsAsGarage()
    {
        return $this->hasMany(ServiceBooking::class, 'garage_id');
    }

    public function serviceBookingsAsCustomer()
    {
        return $this->hasMany(ServiceBooking::class, 'user_id');
    }

    /**
     * Onboarding mitra (showroom / bengkel): satu baris per user.
     */
    public function merchantProfile()
    {
        return $this->hasOne(MerchantProfile::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'community_members')->withPivot('role')->withTimestamps();
    }

    const STATUS_ACTIVE = 'enable';

    const STATUS_INACTIVE = 'disable';

    const BANNED_ACTIVE = 'yes';

    const BANNED_INACTIVE = 'no';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'country',
        'address',
        'latitude',
        'longitude',
        'designation',
        'specialization',
        'status',
        'is_banned',
        'is_influencer',
        'is_dealer',
        'is_garage',
        'is_mediator',
        'is_sales',
        'sales_partner_type',
        'partner_id',
        'showroom_id',
        'barcode',
        'password',
        'verification_token',
        'provider',
        'provider_id',
        'email_verified_at',
        'verification_otp',
        'date_of_birth',
        'gender',
        'operating_hours',
        'instagram',
        'facebook',
        'twitter',
        'linkedin',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
        'cars',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
