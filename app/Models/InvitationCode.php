<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationCode extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'code',
        'is_active',
        'max_uses',
        'uses_count',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->uses_count >= $this->max_uses) {
            return false;
        }

        return true;
    }
}
