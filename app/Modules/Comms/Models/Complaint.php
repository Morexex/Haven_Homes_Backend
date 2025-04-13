<?php

namespace App\Modules\Comms\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PropertyUser;
use App\Modules\Comms\Models\ComplaintMessage;

class Complaint extends Model
{
    protected $guarded = [];

    public function complainant()
    {
        return $this->belongsTo(PropertyUser::class, 'complainant_id');
    }

    public function assignee()
    {
        return $this->belongsTo(PropertyUser::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(ComplaintMessage::class, 'complaint_id');
    }
}
