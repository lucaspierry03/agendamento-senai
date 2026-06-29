<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $repository,
        private UserService $service
    ) {}

    public function index(): JsonResponse
    {
        $users = $this->repository->all();
        return response()->json(UserResource::collection($users));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->service->create($request->validated());
        return response()->json(new UserResource($user), 201);
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->repository->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $data = $request->validated();

        // Atendente não pode alterar role
        if ($request->user()->isAttendant()) {
            unset($data['role']);
        }

        $user = $this->service->update($user, $data);
        return response()->json(new UserResource($user));
    }

    public function destroy(int $id): JsonResponse
    {
        $user = $this->repository->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Não é possível excluir seu próprio usuário.'], 403);
        }

        $this->service->delete($user);
        return response()->json(['message' => 'Usuário excluído com sucesso.']);
    }
}
