<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromoCode;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;


class PromoCodeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        PromoCode::truncate();

        $faker = \Faker\Factory::create();

        //$user = User::factory()->create();
        //$user->generateToken();
        $user_id = User::all()->pluck('id')->last();

        $mytime = Carbon::now()->add(env('PROMO_CODE_EXPIRY', 0), 'hour');
        //$mytime->toDateTimeString();

        // And now, let's create a few promo codes in our database:
        for ($i = 0; $i < 15; $i++) {
            $code = PromoCode::create([
                //'promo_code' => $this->generatePromoCode(8), //$faker->sentence,
                'amount' => $faker->randomFloat(1, 100, 500),
                'event_id' => Event::all()->pluck('id')->first(),
                'is_active' => '1',
                'expiry' => $mytime,
                'created_by' => $user_id,
            ]);
            $code->generatePromoCode();
        }
    }
}
