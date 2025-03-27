<?php

namespace App\Modules\Comms\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PropertyUser;

class Complaint extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(PropertyUser::class, 'complainant_id');
    }

    public function assignee()
    {
        return $this->belongsTo(PropertyUser::class, 'assigned_to');
    }
}
