<?php

namespace App\Modules\Comms\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PropertyUser;

class ComplaintMessage extends Model
{
    protected $guarded = [];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function sender()
    {
        return $this->belongsTo(PropertyUser::class, 'user_id');
    }
}

