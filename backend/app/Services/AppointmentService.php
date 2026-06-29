<?php

namespace App\Services;

use App\Models\Appointment;
use App\Repositories\AppointmentRepository;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    public function __construct(
        private AppointmentRepository $repository
    ) {}

    public function create(array $data): Appointment
    {
        $hasConflict = $this->repository->hasConflict(
            $data['user_id'],
            $data['date'],
            $data['start_time'],
            $data['end_time']
        );

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'start_time' => ['Este horário já está ocupado para o atendente selecionado.'],
            ]);
        }

        $appointment = $this->repository->create($data);
        AuditService::log('created', $appointment, null, $data);

        return $appointment->load(['user', 'client']);
    }

    public function cancel(Appointment $appointment): Appointment
    {
        $oldValues = $appointment->only(['status']);
        $appointment = $this->repository->cancel($appointment);
        AuditService::log('deleted', $appointment, $oldValues, ['status' => 'cancelled']);

        return $appointment;
    }
}
