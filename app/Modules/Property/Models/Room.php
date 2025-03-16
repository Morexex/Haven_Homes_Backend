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

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(RoomCategory::class);
    }
    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    public function propertyUsers()
    {
        return $this->hasMany(PropertyUser::class);
    }

    public function roomCharges()
    {
        return $this->hasMany(RoomCharge::class);
    }

    public function rentalAgreements()
    {
        return $this->hasMany(RentalAgreement::class);
    }

    public function calculateTotalRoomCharges()
    {

        $totalCharges = 0;

        foreach ($this->roomCharges as $charge) {
            $totalCharges += $charge->amount;
        }


        return $totalCharges;

    }

    public function roomDetailsArray()
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'vacancy_status' => $this->is_vacant,
            'description' => $this->description,
            'category' => $this->category->label,
            'quantity' => $this->quantity,
            'floor' => $this->floor,
            'user' => $this->propertyUsers->first(),
            'rooms_amenities' => $this->category->amenities,
            'room_charges' => $this->roomCharges,
            'total_charges' => $this->calculateTotalRoomCharges(),
            'agreements' => $this->rentalAgreements->first() ? $this->rentalAgreements->first()->roomAgreementArray() : null,
        ];
    }
}

