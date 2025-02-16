<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'is_vacant', 'description', 'category_id', 'quantity', 'floor'];

    public function category()
    {
        return $this->belongsTo(RoomCategory::class, 'category_id');
    }
}

