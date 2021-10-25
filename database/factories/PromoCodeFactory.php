<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PromoCodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PromoCode::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Create event to associate a Promo Code with
        $event = Event::factory()->create();
        $user = User::factory()->create();
        $mytime = Carbon::now()->add(env('PROMO_CODE_EXPIRY', 0), 'hour');

        $data['expiry'] = $mytime;

        return [
            'promo_code' => $this->generatePromoCode(8),
            'amount' => $this->faker->sentence,
            'event_id' => $event->id,
            'expiry' => $mytime,
            'created_by' => $user->id,
        ];
    }

    /**
     * Generate random code for a given length
     * 
     * @param integer $codeLength
     * @return string
     */
    private function generatePromoCode($codeLength) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);

        $promoCode = '';

        while (strlen($promoCode) < $codeLength) {
            $position = rand(0, $charactersNumber - 1);
            $character = $characters[$position];
            $promoCode = $promoCode.$character;
        }

        if (PromoCode::where('promo_code', $promoCode)->exists()) {
            $this->generatePromoCode($codeLength);
        }

        return $promoCode;
    }

}
