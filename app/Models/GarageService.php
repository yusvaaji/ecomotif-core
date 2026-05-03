<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GarageService extends Model
{
    use HasFactory;

    protected $fillable = [
        'garage_id',
        'name',
        'description',
        'price',
        'duration',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function spareparts()
    {
        return $this->hasMany(GarageServiceSparepart::class);
    }

    public function garage()
    {
        return $this->belongsTo(User::class, 'garage_id');
    }

    public function bookings()
    {
        return $this->hasMany(ServiceBooking::class);
    }
}
