<?php

namespace App\Modules\Comms\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReview extends Model
{
    protected $guarded = [];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user()
    {
        return $this->belongsTo(PropertyUser::class, 'user_id');
    }
}
