<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;

class EventTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Event::truncate();

        $faker = \Faker\Factory::create();

        //$user = User::factory()->create();
        //$user->generateToken();
        $user_id = User::all()->pluck('id')->last();

        // And now, let's create a few articles in our database:
        for ($i = 0; $i < 5; $i++) {
            Event::create([
                'event_name' => $faker->sentence,
                'event_description' => $faker->paragraph,
                'latitude' => "-1.266298",
                'longitude' => "36.763025",
                'location_name' => "Shani Taji School, Musa Gitau Rd, Nairobi City",
                'created_by' => $user_id,
            ]);
        }
    }
}
