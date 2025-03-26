<?php

namespace App\Modules\Comms\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Comms\Models\BulkCommunicationRecipient;
use App\Models\PropertyUser;

class BulkCommunicationRecipient extends Model
{
    protected $guarded = [];

    public function recipients()
    {
        return $this->belongsTo(BulkCommunicationRecipient::class, 'bulk_communication_id');
    }

    public function user()
    {
        return $this->belongsTo(PropertyUser::class, 'user_id');
    }
}
