<?php

namespace App\Models;

use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'event_name',
        'event_description',
        'latitude',
        'longitude',
        'location_name',
        'created_by',
    ];

    /**
     * Fetch event latitude coordinate
     *
     * @return @var string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Fetch event longitude coordinate
     *
     * @return @var string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

}
