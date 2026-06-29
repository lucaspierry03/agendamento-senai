<?php

namespace App\Repositories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Collection;

class AppointmentRepository
{
    public function all(?int $userId = null): Collection
    {
        $query = Appointment::with(['user', 'client']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('date')->orderBy('start_time')->get();
    }

    public function findByUserAndDate(int $userId, string $date): Collection
    {
        return Appointment::where('user_id', $userId)
            ->where('date', $date)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->get();
    }

    public function find(int $id): ?Appointment
    {
        return Appointment::with(['user', 'client'])->find($id);
    }

    public function create(array $data): Appointment
    {
        return Appointment::create($data);
    }

    public function cancel(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => 'cancelled']);
        $appointment->delete(); // soft delete
        return $appointment->fresh();
    }

    public function hasConflict(int $userId, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $query = Appointment::where('user_id', $userId)
            ->where('date', $date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                   ->where('end_time', '>', $startTime);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
