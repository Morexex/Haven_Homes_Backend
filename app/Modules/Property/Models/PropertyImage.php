<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'image_path', 'tag'];

    // Relationship: Each image belongs to a property
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
