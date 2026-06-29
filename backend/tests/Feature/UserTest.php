<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $attendant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->attendant = User::factory()->create(['role' => 'attendant']);
    }

    public function test_admin_can_list_users(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/users');

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_attendant_can_list_users(): void
    {
        $response = $this->actingAs($this->attendant)->getJson('/api/users');

        $response->assertOk();
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/users', [
            'name' => 'Novo Usuário',
            'email' => 'novo@email.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'attendant',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Novo Usuário');

        $this->assertDatabaseHas('users', ['email' => 'novo@email.com']);
    }

    public function test_attendant_cannot_create_user(): void
    {
        $response = $this->actingAs($this->attendant)->postJson('/api/users', [
            'name' => 'Tentativa',
            'email' => 'tentativa@email.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'attendant',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_user(): void
    {
        $response = $this->actingAs($this->admin)->putJson("/api/users/{$this->attendant->id}", [
            'name' => 'Nome Atualizado',
            'role' => 'attendant',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Nome Atualizado');
    }

    public function test_attendant_can_update_own_profile(): void
    {
        $response = $this->actingAs($this->attendant)->putJson("/api/users/{$this->attendant->id}", [
            'name' => 'Meu Nome Novo',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Meu Nome Novo');
    }

    public function test_attendant_cannot_update_other_user(): void
    {
        $response = $this->actingAs($this->attendant)->putJson("/api/users/{$this->admin->id}", [
            'name' => 'Hack',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_user(): void
    {
        $response = $this->actingAs($this->admin)->deleteJson("/api/users/{$this->attendant->id}");

        $response->assertOk();
        $this->assertSoftDeleted('users', ['id' => $this->attendant->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->deleteJson("/api/users/{$this->admin->id}");

        $response->assertStatus(403);
    }

    public function test_attendant_cannot_delete_user(): void
    {
        $response = $this->actingAs($this->attendant)->deleteJson("/api/users/{$this->admin->id}");

        $response->assertStatus(403);
    }

    public function test_create_user_validates_email_unique(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/users', [
            'name' => 'Duplicado',
            'email' => $this->attendant->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'attendant',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_create_user_validates_password_min_length(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/users', [
            'name' => 'Short Pass',
            'email' => 'short@email.com',
            'password' => '123',
            'password_confirmation' => '123',
            'role' => 'attendant',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }
}
