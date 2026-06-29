<?php

namespace App\Repositories;

use App\Models\Availability;
use Illuminate\Database\Eloquent\Collection;

class AvailabilityRepository
{
    public function all(?int $userId = null): Collection
    {
        $query = Availability::with('user');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('user_id')->orderBy('day_of_week')->orderBy('start_time')->get();
    }

    public function findByUserAndDay(int $userId, int $dayOfWeek): Collection
    {
        return Availability::where('user_id', $userId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();
    }

    public function find(int $id): ?Availability
    {
        return Availability::find($id);
    }

    public function create(array $data): Availability
    {
        return Availability::create($data);
    }

    public function update(Availability $availability, array $data): Availability
    {
        $availability->update($data);
        return $availability->fresh();
    }

    public function delete(Availability $availability): void
    {
        $availability->delete();
    }
}
