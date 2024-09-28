<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;

interface ProductRepositoryInterface
{
    public function find(int $id): ?Product;

    public function updateStock(int $productId, int $quantity): bool;
}
