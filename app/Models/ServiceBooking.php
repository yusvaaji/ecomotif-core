<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'user_id',
        'garage_id',
        'garage_service_id',
        'service_ids',
        'service_type',
        'booking_date',
        'booking_time',
        'customer_name',
        'customer_phone',
        'customer_address',
        'location_benchmark',
        'vehicle_brand',
        'vehicle_model',
        'vehicle_year',
        'vehicle_plate',
        'notes',
        'status',
        'total_price',
        'garage_notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'total_price' => 'decimal:2',
        'service_ids' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function garage()
    {
        return $this->belongsTo(User::class, 'garage_id');
    }

    public function service()
    {
        return $this->belongsTo(GarageService::class, 'garage_service_id');
    }
}
