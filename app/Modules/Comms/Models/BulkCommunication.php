<?php

namespace App\Modules\Comms\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Comms\Models\BulkCommunicationRecipient;

class BulkCommunication extends Model
{
    protected $guarded = [];

    public function recipients()
    {
        return $this->hasMany(BulkCommunicationRecipient::class);
    }
}
