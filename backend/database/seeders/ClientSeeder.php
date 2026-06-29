<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['name' => 'Carlos Oliveira', 'phone' => '(48) 99901-1234', 'email' => 'carlos@email.com'],
            ['name' => 'Ana Costa', 'phone' => '(48) 99902-5678', 'email' => 'ana@email.com'],
            ['name' => 'Pedro Souza', 'phone' => '(48) 99903-9012', 'email' => 'pedro@email.com'],
            ['name' => 'Juliana Lima', 'phone' => '(48) 99904-3456', 'email' => 'juliana@email.com'],
            ['name' => 'Roberto Alves', 'phone' => '(48) 99905-7890', 'email' => 'roberto@email.com'],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
