<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@email.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'password' => 'password',
            'role' => 'attendant',
        ]);

        User::create([
            'name' => 'Maria Santos',
            'email' => 'maria@email.com',
            'password' => 'password',
            'role' => 'attendant',
        ]);
    }
}
