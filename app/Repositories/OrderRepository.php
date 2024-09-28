<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Create a new order.
     *
     * @param array $data The data for the new order.
     * @return Order The created order instance.
     * @throws Exception If there is an error during order creation.
     */
    public function create(array $data): Order
    {
        try {
            $order = Order::create($data);
            Log::info('Order created successfully.', ['order_id' => $order->id]);
            return $order;
        } catch (Exception $e) {
            Log::error('Failed to create order: ' . $e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }

    /**
     * Update an existing order.
     *
     * @param int $id The ID of the order to update.
     * @param array $data The data to update the order with.
     * @return bool True if the order was updated, false otherwise.
     * @throws Exception If there is an error during order update.
     */
    public function update(int $id, array $data): bool
    {
        try {
            $order = Order::find($id);
            if ($order) {
                $updated = $order->update($data);
                Log::info('Order updated successfully.', ['order_id' => $id]);
                return $updated;
            }
            Log::warning('Order not found for update.', ['order_id' => $id]);
            return false;
        } catch (Exception $e) {
            Log::error('Failed to update order: ' . $e->getMessage(), ['order_id' => $id, 'data' => $data]);
            throw $e;
        }
    }

    /**
     * Delete an existing order.
     *
     * @param int $id The ID of the order to delete.
     * @return bool True if the order was deleted, false otherwise.
     * @throws Exception If there is an error during order deletion.
     */
    public function delete(int $id): bool
    {
        try {
            $order = Order::find($id);
            if ($order) {
                $deleted = $order->delete();
                Log::info('Order deleted successfully.', ['order_id' => $id]);
                return $deleted;
            }
            Log::warning('Order not found for deletion.', ['order_id' => $id]);
            return false;
        } catch (Exception $e) {
            Log::error('Failed to delete order: ' . $e->getMessage(), ['order_id' => $id]);
            throw $e;
        }
    }

    /**
     * Retrieve all orders with optional trashed orders.
     *
     * @param bool $withTrashed Whether to include trashed orders.
     * @return \Illuminate\Database\Eloquent\Builder The query builder instance for orders.
     */
    public function getAll(bool $withTrashed = false)
    {
        return $withTrashed ? Order::withTrashed() : Order::query();
    }

    /**
     * Retrieve all orders for a specific user.
     *
     * @param int $userId The ID of the user.
     * @return \Illuminate\Database\Eloquent\Builder The query builder instance for user orders.
     */
    public function getUserOrders(int $userId)
    {
        return Order::with('user')->where('user_id', $userId);
    }
    
    /**
     * Retrieve an order by its ID.
     *
     * @param int $id The ID of the order.
     * @return Order|null The order instance or null if not found.
     */
    public function getById(int $id): ?Order
    {
        return Order::with('user')->find($id);
    }
}
