<?php

namespace App\Modules\Property\Models;

use Illuminate\Database\Eloquent\Model;

class RentalAgreement extends Model
{
    protected $guarded = [];
    protected $casts = [
        'payment_date' => 'date',
        'tenancy_start_date' => 'date',
        'charges_agreement' => 'array',
        'amenities_agreement' => 'array',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // In your RentalAgreement model or Room model
    public function getIdImages()
    {
        // Check if id_front and id_back exist in the agreement data
        $idFrontPath = $this->id_front;
        $idBackPath = $this->id_back;

        // Return the full URL for both id images
        return [
            'id_front_url' => $idFrontPath ? asset("storage/{$idFrontPath}") : null,
            'id_back_url' => $idBackPath ? asset("storage/{$idBackPath}") : null,
        ];
    }

    public function humanReadableDate($date)
    {
        return $date->format('jS F Y');
    }


    public function roomAgreementArray()
    {
        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'payment_date' => $this->humanReadableDate($this->payment_date),
            'tenancy_start_date' => $this->humanReadableDate($this->tenancy_start_date),
            'tenant_name' => $this->tenant_name,
            'tenant_email' => $this->tenant_email,
            'tenant_phone' => $this->tenant_phone,
            'room_agreement' => $this->room_agreement,
            'room_decline_reason' => $this->room_decline_reason,
            'charges_agreement' => $this->charges_agreement,
            'amenities_agreement' => $this->amenities_agreement,
            // Get the URLs for the front and back images
            'id_front_url' => $this->getIdImages()['id_front_url'],
            'id_back_url' => $this->getIdImages()['id_back_url'],
        ];
    }
}
