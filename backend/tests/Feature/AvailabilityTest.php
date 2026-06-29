<?php

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
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

    public function test_admin_can_create_availability(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/availabilities', [
            'user_id' => $this->attendant->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '12:00',
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('availabilities', [
            'user_id' => $this->attendant->id,
            'day_of_week' => 1,
        ]);
    }

    public function test_attendant_cannot_create_availability(): void
    {
        $response = $this->actingAs($this->attendant)->postJson('/api/availabilities', [
            'user_id' => $this->attendant->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '12:00',
            'is_active' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/availabilities', [
            'user_id' => $this->attendant->id,
            'day_of_week' => 1,
            'start_time' => '12:00',
            'end_time' => '08:00',
            'is_active' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('end_time');
    }

    public function test_admin_can_update_availability(): void
    {
        $availability = Availability::factory()->create(['user_id' => $this->attendant->id]);

        $response = $this->actingAs($this->admin)->putJson("/api/availabilities/{$availability->id}", [
            'user_id' => $this->attendant->id,
            'day_of_week' => 2,
            'start_time' => '09:00',
            'end_time' => '13:00',
            'is_active' => true,
        ]);

        $response->assertOk();
    }

    public function test_admin_can_delete_availability(): void
    {
        $availability = Availability::factory()->create(['user_id' => $this->attendant->id]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/availabilities/{$availability->id}");

        $response->assertOk();
        $this->assertSoftDeleted('availabilities', ['id' => $availability->id]);
    }
}
