<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Modules\Property\Models\Room;

class PropertyUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $guarded = [];
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'phone',
    //     'password',
    //     'role',
    //     'status',
    //     'room_id',
    // ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'id', 'rood_id');
    }
}

