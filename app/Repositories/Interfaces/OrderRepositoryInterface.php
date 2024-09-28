<?php

namespace App\Repositories\Interfaces;
              

use App\Models\Order;

interface OrderRepositoryInterface
{
    public function create(array $data);
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getAll();
    public function getUserOrders(int $userId);
    public function getById(int $id): ?Order;
}
