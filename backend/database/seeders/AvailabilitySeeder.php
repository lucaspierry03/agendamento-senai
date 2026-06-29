<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\User;
use Illuminate\Database\Seeder;

class AvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $attendants = User::where('role', 'attendant')->get();

        foreach ($attendants as $attendant) {
            for ($day = 1; $day <= 5; $day++) {
                Availability::firstOrCreate(
                    ['user_id' => $attendant->id, 'day_of_week' => $day, 'start_time' => '08:00'],
                    ['end_time' => '12:00', 'is_active' => true]
                );

                Availability::firstOrCreate(
                    ['user_id' => $attendant->id, 'day_of_week' => $day, 'start_time' => '13:30'],
                    ['end_time' => '17:30', 'is_active' => true]
                );
            }
        }
    }
}
