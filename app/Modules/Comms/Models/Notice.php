<?php

namespace App\Modules\Comms\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PropertyUser;

class Notice extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(PropertyUser::class, 'user_id');
    }
}
