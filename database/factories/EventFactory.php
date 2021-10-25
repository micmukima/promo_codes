<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
//use Illuminate\Support\Str;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::factory()->create();

        return [
            'event_name' => $this->faker->sentence,
            'event_description' => $this->faker->paragraph,
            'latitude' => "-1.266298",
            'longitude' => "36.763025",
            'location_name' => "Shani Taji School, Musa Gitau Rd, Nairobi City",
            'created_by' => $user->id,
        ];
    }   
}
