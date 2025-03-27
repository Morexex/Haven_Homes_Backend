<?php

namespace App\Modules\Comms\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PropertyUser;

class Vacation extends Model
{
    protected $guarded = [];

    public function tenant()
    {
        return $this->belongsTo(PropertyUser::class, 'user_id');
    }

    public function room()
    {
        return $this->belongsTo(PropertyUser::class, 'room_id');
    }
}
