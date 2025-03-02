<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Property\Models\RoomImage;
use App\Modules\Property\Models\RoomCategory;
use App\Models\PropertyUser;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'is_vacant', 'description', 'category_id', 'quantity', 'floor'];

    public function category()
    {
        return $this->belongsTo(RoomCategory::class, 'category_id');
    }
    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    public function propertyUsers()
    {
        return $this->hasMany(PropertyUser::class);
    }
}

