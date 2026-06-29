<?php

namespace App\Services;

use App\Repositories\AppointmentRepository;
use App\Repositories\AvailabilityRepository;
use Carbon\Carbon;

class SlotService
{
    private const SLOT_DURATION = 30;

    public function __construct(
        private AvailabilityRepository $availabilityRepository,
        private AppointmentRepository $appointmentRepository
    ) {}

    public function getAvailableSlots(int $userId, string $date): array
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek;

        $availabilities = $this->availabilityRepository->findByUserAndDay($userId, $dayOfWeek);

        if ($availabilities->isEmpty()) {
            return [];
        }

        $appointments = $this->appointmentRepository->findByUserAndDate($userId, $date);

        $slots = [];
        foreach ($availabilities as $availability) {
            $start = Carbon::parse($availability->start_time);
            $end = Carbon::parse($availability->end_time);

            while ($start->copy()->addMinutes(self::SLOT_DURATION)->lte($end)) {
                $slotStart = $start->format('H:i');
                $slotEnd = $start->copy()->addMinutes(self::SLOT_DURATION)->format('H:i');

                $isOccupied = $appointments->contains(function ($appt) use ($slotStart, $slotEnd) {
                    $apptStart = substr($appt->start_time, 0, 5);
                    $apptEnd = substr($appt->end_time, 0, 5);
                    return $apptStart < $slotEnd && $apptEnd > $slotStart;
                });

                if (!$isOccupied) {
                    $slots[] = [
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd,
                    ];
                }

                $start->addMinutes(self::SLOT_DURATION);
            }
        }

        return $slots;
    }
}
