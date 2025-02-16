<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = ['property_name', 'property_code', 'property_address', ''];

    // Define a relationship to the User model
    public function admin_users()
    {
        return $this->hasMany(AdminUser::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(PropertyUser::class);
    }
}

