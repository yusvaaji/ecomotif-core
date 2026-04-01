<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'image',
        'cover_image',
        'location',
        'privacy',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'community_members')->withPivot('role')->withTimestamps();
    }

    public function posts()
    {
        return $this->hasMany(CommunityPost::class);
    }
}
