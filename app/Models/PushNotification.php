<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'admin_id',
        'status',
        'target_type',
        'target_ids',
        'recipients'
    ];

    protected $casts = [
        'target_ids' => 'array'
    ];
}
