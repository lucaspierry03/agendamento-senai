<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        if (Appointment::count() > 0) {
            return;
        }

        $attendants = User::where('role', 'attendant')->get();
        $clients = Client::all();
        $today = Carbon::today();

        $appointments = [
            ['days' => 0, 'start' => '08:00', 'end' => '08:30', 'attendant' => 0, 'client' => 0],
            ['days' => 0, 'start' => '09:30', 'end' => '10:00', 'attendant' => 0, 'client' => 1],
            ['days' => 0, 'start' => '14:00', 'end' => '14:30', 'attendant' => 1, 'client' => 2],
            ['days' => 1, 'start' => '10:00', 'end' => '10:30', 'attendant' => 0, 'client' => 3],
            ['days' => 1, 'start' => '15:00', 'end' => '15:30', 'attendant' => 1, 'client' => 4],
        ];

        foreach ($appointments as $data) {
            $date = $today->copy()->addDays($data['days']);

            if ($date->isWeekend()) {
                $date = $date->nextWeekday();
            }

            Appointment::create([
                'user_id' => $attendants[$data['attendant']]->id,
                'client_id' => $clients[$data['client']]->id,
                'date' => $date->format('Y-m-d'),
                'start_time' => $data['start'],
                'end_time' => $data['end'],
                'status' => 'scheduled',
            ]);
        }
    }
}
