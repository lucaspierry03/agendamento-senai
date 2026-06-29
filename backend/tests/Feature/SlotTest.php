<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotTest extends TestCase
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

    public function test_returns_available_slots(): void
    {
        // Segunda-feira (day_of_week = 1)
        Availability::factory()->create([
            'user_id' => $this->attendant->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'is_active' => true,
        ]);

        // Próxima segunda-feira
        $nextMonday = Carbon::now()->next(Carbon::MONDAY)->format('Y-m-d');

        $response = $this->actingAs($this->admin)->getJson("/api/slots?user_id={$this->attendant->id}&date={$nextMonday}");

        $response->assertOk();

        // 08:00-10:00 = 4 slots de 30min (08:00, 08:30, 09:00, 09:30)
        $this->assertCount(4, $response->json());
    }

    public function test_excludes_occupied_slots(): void
    {
        Availability::factory()->create([
            'user_id' => $this->attendant->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'is_active' => true,
        ]);

        $nextMonday = Carbon::now()->next(Carbon::MONDAY)->format('Y-m-d');
        $client = Client::factory()->create();

        // Ocupa slot 08:00-08:30
        Appointment::factory()->create([
            'user_id' => $this->attendant->id,
            'client_id' => $client->id,
            'date' => $nextMonday,
            'start_time' => '08:00:00',
            'end_time' => '08:30:00',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->admin)->getJson("/api/slots?user_id={$this->attendant->id}&date={$nextMonday}");

        $response->assertOk();
        // 3 slots restantes (08:30, 09:00, 09:30)
        $slots = $response->json();
        $this->assertCount(3, $slots);
        $this->assertEquals('08:30', $slots[0]['start_time']);
    }

    public function test_returns_empty_for_day_without_availability(): void
    {
        // Atendente sem disponibilidade no domingo (0)
        $nextSunday = Carbon::now()->next(Carbon::SUNDAY)->format('Y-m-d');

        $response = $this->actingAs($this->admin)->getJson("/api/slots?user_id={$this->attendant->id}&date={$nextSunday}");

        $response->assertOk();
        $this->assertCount(0, $response->json());
    }

    public function test_inactive_availability_is_not_considered(): void
    {
        Availability::factory()->create([
            'user_id' => $this->attendant->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'is_active' => false,
        ]);

        $nextMonday = Carbon::now()->next(Carbon::MONDAY)->format('Y-m-d');

        $response = $this->actingAs($this->admin)->getJson("/api/slots?user_id={$this->attendant->id}&date={$nextMonday}");

        $response->assertOk();
        $this->assertCount(0, $response->json());
    }
}
