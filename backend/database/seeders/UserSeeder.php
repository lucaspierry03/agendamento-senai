<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@email.com'],
            ['name' => 'Administrador', 'password' => 'password', 'role' => 'admin']
        );

        User::firstOrCreate(
            ['email' => 'joao@email.com'],
            ['name' => 'João Silva', 'password' => 'password', 'role' => 'attendant']
        );

        User::firstOrCreate(
            ['email' => 'maria@email.com'],
            ['name' => 'Maria Santos', 'password' => 'password', 'role' => 'attendant']
        );
    }
}
