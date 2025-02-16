<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'description', 'size', 'image', 'color', 'condition', 'category_id'];

    public function category()
    {
        return $this->belongsTo(RoomCategory::class, 'category_id');
    }
}
