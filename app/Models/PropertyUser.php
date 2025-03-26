<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Modules\Property\Models\Room;
use App\Modules\Comms\Models\Complaint;
use App\Modules\Comms\Models\Notice;
use App\Modules\Comms\Models\Vacation;
use App\Modules\Comms\Models\BulkCommunicationRecipient;

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

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'complainant_id', 'id');
    }

    public function notices()
    {
        return $this->hasMany(Notice::class, 'user_id', 'id');
    }

    public function vacations()
    {
        return $this->hasMany(Vacation::class, 'user_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(BulkCommunicationRecipient::class, 'user_id', 'id');
    }
}

