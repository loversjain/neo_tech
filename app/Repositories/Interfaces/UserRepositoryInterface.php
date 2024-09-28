<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function create(array $data): User;
    public function update(int $id, array $data): bool;
    public function getAll(): array;
    public function getById(int $id): ?User;
}
