<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    use HasFactory;

    protected $fillable = ['room_id', 'image_path', 'tag'];

    // Relationship: Each image belongs to a property
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
