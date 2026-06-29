<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $date = Carbon::now()->next(Carbon::MONDAY);

        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'date' => $date->format('Y-m-d'),
            'start_time' => '08:00:00',
            'end_time' => '08:30:00',
            'status' => 'scheduled',
            'notes' => null,
        ];
    }
}
