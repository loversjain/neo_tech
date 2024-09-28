<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Find a product by its ID.
     *
     * @param int $id The ID of the product to find.
     * @return Product|null The found product or null if not found.
     * @throws ModelNotFoundException If the product is not found.
     */
    public function find(int $id): ?Product
    {
        $product = Product::find($id);
        
        // If the product is not found, log and throw an exception
        if (!$product) {
            Log::error('Product not found.', ['product_id' => $id]);
            throw new ModelNotFoundException("Product with ID $id not found.");
        }

        return $product;
    }

    /**
     * Update the stock for a specific product.
     *
     * @param int $productId The ID of the product to update.
     * @param int $quantity The quantity to deduct from the stock.
     * @return bool True if the stock was updated successfully, false otherwise.
     * @throws ModelNotFoundException If the product is not found.
     * @throws Exception If an unexpected error occurs during stock update.
     */
    public function updateStock(int $productId, int $quantity): bool
    {
        try {
            $product = $this->find($productId);
            
            if ($product->stock >= $quantity) {
                $product->stock -= $quantity;  // Deduct the ordered quantity from stock
                $product->save();
                Log::info("Product stock updated successfully.", [
                    'product_id' => $productId,
                    'quantity_deducted' => $quantity,
                    'new_stock' => $product->stock,
                ]);
                return true;
            }

            Log::warning('Failed to update product stock, insufficient stock.', [
                'product_id' => $productId,
                'requested_quantity' => $quantity,
                'available_stock' => $product->stock,
            ]);
            return false;
        } catch (ModelNotFoundException $e) {
            Log::error('Error while updating stock.', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
            ]);
            throw $e;
        } catch (Exception $e) {
            Log::error('Failed to update product stock due to an unexpected error.', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            throw new Exception("An unexpected error occurred while updating the product stock.");
        }
    }
}
