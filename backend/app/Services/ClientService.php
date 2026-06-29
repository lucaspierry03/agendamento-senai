<?php

namespace App\Services;

use App\Models\Client;
use App\Repositories\ClientRepository;

class ClientService
{
    public function __construct(
        private ClientRepository $repository
    ) {}

    public function create(array $data): Client
    {
        $client = $this->repository->create($data);
        AuditService::log('created', $client, null, $data);
        return $client;
    }

    public function update(Client $client, array $data): Client
    {
        $oldValues = $client->only(['name', 'phone', 'email']);
        $client = $this->repository->update($client, $data);
        AuditService::log('updated', $client, $oldValues, $data);
        return $client;
    }

    public function delete(Client $client): void
    {
        AuditService::log('deleted', $client, $client->toArray());
        $this->repository->delete($client);
    }
}
