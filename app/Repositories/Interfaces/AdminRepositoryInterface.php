<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface AdminRepositoryInterface
{
    public function manageUser(int $id): void;
    public function getById(int $id): ?User;
}
