<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class AdminUser extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $guarded = [];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Relationship with Property (Each user belongs to a property)
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Additional helper methods can go here (like checking roles, etc.)
}

