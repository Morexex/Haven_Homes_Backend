<?php

namespace App\Modules\Comms\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Comms\Models\BulkCommunicationRecipient;
use App\Models\AdminUser;

class BulkCommunication extends Model
{
    protected $guarded = [];

    public function recipients()
    {
        return $this->hasMany(BulkCommunicationRecipient::class);
    }

    public function creator()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }
}
