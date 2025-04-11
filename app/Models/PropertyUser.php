<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Modules\Property\Models\Room;
use App\Modules\Comms\Models\Complaint;
use App\Modules\Comms\Models\Notice;
use App\Modules\Comms\Models\Vacation;
use App\Modules\Comms\Models\BulkCommunicationRecipient;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyUser extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
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

