<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomCategory extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'description'];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'category_id');
    }

    public function amenities()
    {
        return $this->hasMany(Amenity::class, 'category_id');
    }
}
