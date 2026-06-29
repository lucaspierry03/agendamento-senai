<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Repositories\AppointmentRepository;
use App\Services\AppointmentService;
use App\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentRepository $repository,
        private AppointmentService $service,
        private SlotService $slotService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->query('user_id');
        $appointments = $this->repository->all($userId ? (int) $userId : null);
        return response()->json(AppointmentResource::collection($appointments));
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = $this->service->create($request->validated());
        return response()->json(new AppointmentResource($appointment), 201);
    }

    public function cancel(int $id): JsonResponse
    {
        $appointment = $this->repository->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Agendamento não encontrado.'], 404);
        }

        if ($appointment->isCancelled()) {
            return response()->json(['message' => 'Este agendamento já foi cancelado.'], 422);
        }

        $appointment = $this->service->cancel($appointment);
        return response()->json(['message' => 'Agendamento cancelado com sucesso.']);
    }

    public function slots(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
        ]);

        $slots = $this->slotService->getAvailableSlots(
            (int) $request->query('user_id'),
            $request->query('date')
        );

        return response()->json($slots);
    }
}
