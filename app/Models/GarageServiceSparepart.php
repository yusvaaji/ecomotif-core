<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GarageServiceSparepart extends Model
{
    use HasFactory;

    protected $fillable = [
        'garage_service_id',
        'name',
        'price',
        'stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function garageService()
    {
        return $this->belongsTo(GarageService::class);
    }
}
