<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\Car\Entities\Car;

class Booking extends Model
{
    use HasFactory;

    // Application Types
    const APPLICATION_TYPE_RENTAL = 'rental';
    const APPLICATION_TYPE_LEASING = 'leasing';

    // Leasing Status
    const LEASING_STATUS_PENDING = 'pending';
    const LEASING_STATUS_REVIEW = 'review';
    const LEASING_STATUS_APPROVED = 'approved';
    const LEASING_STATUS_REJECTED = 'rejected';
    const LEASING_STATUS_APPEALED = 'appealed';

    // Booking Status (existing)
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELLED_BY_USER = 3;
    const STATUS_CANCELLED_BY_DEALER = 4;
    const STATUS_RIDE_STARTED = 6;

    protected $fillable = [
        'order_id',
        'user_id', // consumer
        'supplier_id', // dealer
        'car_id',
        'price',
        'total_price',
        'vat_amount',
        'platform_amount',
        'pickup_location',
        'return_location',
        'pickup_date',
        'pickup_time',
        'return_date',
        'return_time',
        'duration',
        'payment_method',
        'payment_status',
        'transaction',
        'booking_note',
        'status',
        // New fields for leasing
        'application_type',
        'down_payment',
        'installment_amount',
        'mediator_id',
        'marketing_id',
        'showroom_id',
        'leasing_status',
        'leasing_notes',
        'application_documents',
        'pooled_at',
        'appealed_at',
    ];

    protected $casts = [
        'application_documents' => 'array',
        'pooled_at' => 'datetime',
        'appealed_at' => 'datetime',
        'pickup_date' => 'date',
        'return_date' => 'date',
        'down_payment' => 'decimal:2',
        'installment_amount' => 'decimal:2',
    ];

    /**
     * Relationship: Consumer (user who made the booking/application)
     */
    public function consumer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Dealer/Supplier
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Relationship: Mediator (if application created by mediator)
     */
    public function mediator()
    {
        return $this->belongsTo(User::class, 'mediator_id');
    }

    /**
     * Relationship: Marketing (if application created by marketing)
     */
    public function marketing()
    {
        return $this->belongsTo(User::class, 'marketing_id');
    }

    /**
     * Relationship: Showroom (showroom handling the application)
     */
    public function showroom()
    {
        return $this->belongsTo(User::class, 'showroom_id');
    }

    /**
     * Relationship: Car
     */
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    /**
     * Check if application is for leasing
     */
    public function isLeasing()
    {
        return $this->application_type === self::APPLICATION_TYPE_LEASING;
    }

    /**
     * Check if application is for rental
     */
    public function isRental()
    {
        return $this->application_type === self::APPLICATION_TYPE_RENTAL;
    }

    /**
     * Check if leasing status is approved
     */
    public function isLeasingApproved()
    {
        return $this->leasing_status === self::LEASING_STATUS_APPROVED;
    }

    /**
     * Check if can be appealed
     */
    public function canAppeal()
    {
        return $this->leasing_status === self::LEASING_STATUS_REJECTED && $this->appealed_at === null;
    }
}




