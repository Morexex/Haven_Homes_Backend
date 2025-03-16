<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Property\Models\Room;

class RoomCharge extends Model
{
    protected $guarded = [];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
