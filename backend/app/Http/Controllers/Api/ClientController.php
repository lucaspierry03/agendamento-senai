<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(
        private ClientRepository $repository,
        private ClientService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $clients = $this->repository->paginate(15, $request->query('search'));
        return response()->json(ClientResource::collection($clients)->response()->getData(true));
    }

    public function all(): JsonResponse
    {
        $clients = $this->repository->all();
        return response()->json(ClientResource::collection($clients));
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->service->create($request->validated());
        return response()->json(new ClientResource($client), 201);
    }

    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        $client = $this->repository->find($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        $client = $this->service->update($client, $request->validated());
        return response()->json(new ClientResource($client));
    }

    public function destroy(int $id): JsonResponse
    {
        $client = $this->repository->find($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        $this->service->delete($client);
        return response()->json(['message' => 'Cliente excluído com sucesso.']);
    }
}
