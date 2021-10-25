<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'promo_code',
        'amount',
        'expiry',
        'is_active',
        'event_id',
        'created_by',
    ];

    /**
     * Scope a query to only include active codes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

     /**
     * Scope a query to only include codes for a specific event.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEvent($query, $event)
    {
        return $query->where('event_id', $event->id);
    }

    /**
     * Scope a query to only include codes whose expiry date is in the future.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotExpired($query) {
        return $query->where('expiry', '>', Carbon::now());
    }

    /**
     * Fetch event associated with PromoCode
     *
     * @return \App\Models\Event
     */
    public function getEvent()
    {
        return Event::where('id', $this->event_id)
               ->first();
    }

     /**
     * Return validity status of PromoCode
     *
     * code must be active and expiry date must be in future
     * @return boolean
     */
    public function isValid()
    {
        return $this->is_active && $this->expiry > Carbon::now();
    }

    /**
     * generate event promotion code.
     *
     * @return \App\Models\PromoCode
     */
    public function generatePromoCode()
    {
        $this->promo_code = $this->generateCode(env('PROMO_CODE_LENGTH', 10));
        $this->save();

        return $this;
    }

    /**
     * generate random code
     * @param string $codeLength
     * @return string
     */
    private function generateCode($codeLength) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);

        $code = '';

        while (strlen($code) < $codeLength) {
            $position = rand(0, $charactersNumber - 1);
            $character = $characters[$position];
            $code = $code.$character;
        }

        if (PromoCode::where('promo_code', $code)->exists()) {
            $this->generateCode($codeLength);
        }

        return $code;
    }
}
