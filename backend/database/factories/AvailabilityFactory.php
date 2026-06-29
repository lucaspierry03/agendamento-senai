<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailabilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'day_of_week' => fake()->numberBetween(1, 5),
            'start_time' => '08:00',
            'end_time' => '12:00',
            'is_active' => true,
        ];
    }
}
