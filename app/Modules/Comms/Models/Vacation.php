<?php

namespace App\Modules\Comms\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PropertyUser;
use App\Modules\Property\Models\Room;

class Vacation extends Model
{
    protected $guarded = [];

    public function tenant()
    {
        return $this->belongsTo(PropertyUser::class, 'tenant_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
