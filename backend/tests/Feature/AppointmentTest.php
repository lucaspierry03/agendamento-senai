<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $attendant;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->attendant = User::factory()->create(['role' => 'attendant']);
        $this->client = Client::factory()->create();
    }

    public function test_can_create_appointment(): void
    {
        $nextMonday = Carbon::now()->next(Carbon::MONDAY)->format('Y-m-d');

        Availability::factory()->create([
            'user_id' => $this->attendant->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '12:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->postJson('/api/appointments', [
            'user_id' => $this->attendant->id,
            'client_id' => $this->client->id,
            'date' => $nextMonday,
            'start_time' => '08:00',
            'end_time' => '08:30',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'scheduled');

        $this->assertDatabaseHas('appointments', [
            'user_id' => $this->attendant->id,
            'client_id' => $this->client->id,
            'date' => $nextMonday,
        ]);
    }

    public function test_cannot_create_appointment_with_conflict(): void
    {
        $nextMonday = Carbon::now()->next(Carbon::MONDAY)->format('Y-m-d');

        Appointment::factory()->create([
            'user_id' => $this->attendant->id,
            'client_id' => $this->client->id,
            'date' => $nextMonday,
            'start_time' => '08:00',
            'end_time' => '08:30',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->admin)->postJson('/api/appointments', [
            'user_id' => $this->attendant->id,
            'client_id' => $this->client->id,
            'date' => $nextMonday,
            'start_time' => '08:00',
            'end_time' => '08:30',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('start_time');
    }

    public function test_can_cancel_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->attendant->id,
            'client_id' => $this->client->id,
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->admin)->patchJson("/api/appointments/{$appointment->id}/cancel");

        $response->assertOk();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cannot_cancel_already_cancelled_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->attendant->id,
            'client_id' => $this->client->id,
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($this->admin)->patchJson("/api/appointments/{$appointment->id}/cancel");

        $response->assertStatus(422);
    }

    public function test_cannot_create_appointment_in_past(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/appointments', [
            'user_id' => $this->attendant->id,
            'client_id' => $this->client->id,
            'date' => '2020-01-01',
            'start_time' => '08:00',
            'end_time' => '08:30',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('date');
    }
}
