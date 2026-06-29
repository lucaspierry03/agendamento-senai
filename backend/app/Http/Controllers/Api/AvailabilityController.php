<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvailabilityRequest;
use App\Http\Resources\AvailabilityResource;
use App\Repositories\AvailabilityRepository;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(
        private AvailabilityRepository $repository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->query('user_id');
        $availabilities = $this->repository->all($userId ? (int) $userId : null);
        return response()->json(AvailabilityResource::collection($availabilities));
    }

    public function store(StoreAvailabilityRequest $request): JsonResponse
    {
        $availability = $this->repository->create($request->validated());
        AuditService::log('created', $availability, null, $request->validated());
        return response()->json(new AvailabilityResource($availability->load('user')), 201);
    }

    public function update(StoreAvailabilityRequest $request, int $id): JsonResponse
    {
        $availability = $this->repository->find($id);

        if (!$availability) {
            return response()->json(['message' => 'Disponibilidade não encontrada.'], 404);
        }

        $oldValues = $availability->only(['day_of_week', 'start_time', 'end_time', 'is_active']);
        $availability = $this->repository->update($availability, $request->validated());
        AuditService::log('updated', $availability, $oldValues, $request->validated());

        return response()->json(new AvailabilityResource($availability->load('user')));
    }

    public function destroy(int $id): JsonResponse
    {
        $availability = $this->repository->find($id);

        if (!$availability) {
            return response()->json(['message' => 'Disponibilidade não encontrada.'], 404);
        }

        AuditService::log('deleted', $availability, $availability->toArray());
        $this->repository->delete($availability);

        return response()->json(['message' => 'Disponibilidade excluída com sucesso.']);
    }
}
