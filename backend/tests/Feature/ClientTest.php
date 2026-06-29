<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_list_clients(): void
    {
        Client::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/clients');

        $response->assertOk();
    }

    public function test_can_create_client(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/clients', [
            'name' => 'Cliente Teste',
            'phone' => '(48) 99999-0000',
            'email' => 'cliente@email.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Cliente Teste');

        $this->assertDatabaseHas('clients', ['name' => 'Cliente Teste']);
    }

    public function test_create_client_requires_name(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/clients', [
            'phone' => '(48) 99999-0000',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_can_update_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->putJson("/api/clients/{$client->id}", [
            'name' => 'Nome Atualizado',
            'phone' => '(48) 99999-1111',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Nome Atualizado');
    }

    public function test_can_delete_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/clients/{$client->id}");

        $response->assertOk();
        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }

    public function test_can_search_clients(): void
    {
        Client::factory()->create(['name' => 'João Silva']);
        Client::factory()->create(['name' => 'Maria Santos']);

        $response = $this->actingAs($this->user)->getJson('/api/clients?search=João');

        $response->assertOk();
    }
}
