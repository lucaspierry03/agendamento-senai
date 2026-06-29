<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        private UserRepository $repository
    ) {}

    public function create(array $data): User
    {
        $user = $this->repository->create($data);
        AuditService::log('created', $user, null, $data);
        return $user;
    }

    public function update(User $user, array $data): User
    {
        $oldValues = $user->only(['name', 'role']);
        $user = $this->repository->update($user, $data);
        AuditService::log('updated', $user, $oldValues, $data);
        return $user;
    }

    public function delete(User $user): void
    {
        AuditService::log('deleted', $user, $user->toArray());
        $this->repository->delete($user);
    }
}
