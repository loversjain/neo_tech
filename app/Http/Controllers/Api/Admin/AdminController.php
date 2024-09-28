<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\Admin\UpdateProductStockRequest;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Enums\ResponseMessage;
use App\Enums\StatusCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdminController extends Controller
{
    /**
     * Create a new instance of the AdminController.
     *
     * @param AdminRepositoryInterface $adminRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        protected AdminRepositoryInterface $adminRepository, 
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * Manage the active status of a user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function manageUser(int $id): JsonResponse
    {
        try {
            // Check if user exists
            $user = $this->adminRepository->getById($id);
            if (!$user) {
                Log::warning('User not found for status update.', ['user_id' => $id]);
                return response()->json(['message' => ResponseMessage::USER_NOT_FOUND->value], StatusCode::NOT_FOUND->value);
            }

            // chage active status
            $this->adminRepository->manageUser($id);

            Log::info('User status updated successfully.', ['user_id' => $id]);
            return response()->json(['message' => $this->getUserStatus($user)], StatusCode::OK->value);
            
        } catch (Throwable $e) {
            Log::error('Failed to update user status: ' . $e->getMessage(), ['user_id' => $id]);
            return response()->json(['message' => 'Failed to update user status: ' . $e->getMessage()], StatusCode::INTERNAL_SERVER_ERROR->value);
        }
    }

    /**
     * Check the status of the active user.
     *
     * @param $user
     * @return string
     */
    protected function getUserStatus($user): string
    {
        return ($user->is_active) ? 'User has been deactivated successfully.' : 'User has been activated successfully.';
    }

    /**
     * Retrieve all orders, with an option to include trashed orders.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function allOrders(Request $request): JsonResponse
    {
        try {
            // Retrieve query parameters
            $withTrashed = $this->getWithTrashedParameter($request);
            $perPage = $this->getPerPageParameter($request);

            // Fetch orders with pagination
            $orders = $this->fetchOrders($withTrashed, $perPage);

            // Check for orders and return response
            return $this->createResponse($orders, $request);
        } catch (Throwable $e) {
            return $this->handleFetchOrdersException($e);
        }
    }

    /**
     * Retrieve the 'with_trashed' query parameter.
     *
     * @param Request $request
     * @return bool
     */
    private function getWithTrashedParameter(Request $request): bool
    {
        return $request->query('with_trashed', false);
    }

    /**
     * Retrieve the 'per_page' query parameter with a default value.
     *
     * @param Request $request
     * @return int
     */
    private function getPerPageParameter(Request $request): int
    {
        // Default to 10 items per page
        return (int) $request->query('per_page', 10); 
    }

    /**
     * Fetch orders based on the with_trashed parameter with pagination.
     *
     * @param bool $withTrashed
     * @param int $perPage
     * @return mixed
     */
    private function fetchOrders(bool $withTrashed, int $perPage)
    {
        return $this->orderRepository->getAll($withTrashed)->paginate($perPage);
    }

    /**
     * Create a response based on the fetched orders.
     *
     * @param mixed $orders
     * @param Request $request
     * @return JsonResponse
     */
    private function createResponse($orders, Request $request): JsonResponse
    {
        if ($orders->isEmpty()) {
            Log::info('No orders found.');
            return response()->json(['message' => ResponseMessage::ORDERS_NOT_FOUND->value], StatusCode::NOT_FOUND->value);
        }

        Log::info('Fetched orders successfully.', ['orders_count' => $orders->total()]);

        return response()->json([
            'message' => ResponseMessage::ORDERS_FETCHED->value,
            'data' => $orders->items(),
            'pagination' => $this->getPaginationData($orders, $request),
        ]);
    }

    /**
     * Get pagination data for the response.
     *
     * @param mixed $orders
     * @param Request $request
     * @return array
     */
    private function getPaginationData($orders, Request $request): array
    {
        return [
            'total' => $orders->total(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'from' => $orders->firstItem(),
            'to' => $orders->lastItem(),
            'next_page_url' => $orders->nextPageUrl(),
            'prev_page_url' => $orders->previousPageUrl(),
            'path' => $request->url(),
        ];
    }

    /**
     * Handle exceptions that occur while fetching orders.
     *
     * @param Throwable $e
     * @return JsonResponse
     */
    private function handleFetchOrdersException(Throwable $e): JsonResponse
    {
        Log::error('Failed to fetch orders: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to fetch orders: ' . $e->getMessage()], StatusCode::INTERNAL_SERVER_ERROR->value);
    }

    /**
     * View a specific order by its ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function viewOrder(int $id): JsonResponse
    {
        try {
            // Fetch the order
            $order = $this->orderRepository->getById($id);

            // If order not found, throw exception
            if (!$order) {
                throw new ModelNotFoundException('Order not found.');
            }

            Log::info('Fetched order successfully.', ['order_id' => $id]);
            return response()->json(['data' => $order]);
        } catch (ModelNotFoundException $e) {
            Log::warning('Order not found.', ['order_id' => $id]);
            return response()->json(['message' => ResponseMessage::ORDER_NOT_FOUND->value], StatusCode::NOT_FOUND->value);
        } catch (Throwable $e) {
            Log::error('Failed to fetch order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch order: ' . $e->getMessage()], StatusCode::INTERNAL_SERVER_ERROR->value);
        }
    }

    /**
     * Show a product by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function showProduct(int $id): JsonResponse
    {
        try {
            $product = $this->productRepository->find($id);
            return response()->json($product, StatusCode::OK->value);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::NOT_FOUND->value);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while retrieving the product.'], StatusCode::INTERNAL_SERVER_ERROR->value);
        }
    }

    /**
     * Update the stock for a specific product.
     *
     * @param UpdateProductStockRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateProductStock(UpdateProductStockRequest $request, int $id): JsonResponse
    {
        // Validate quantity
        $quantity = $request->input('quantity');

        try {
            DB::beginTransaction();

            // Find the product first
            $product = $this->productRepository->find($id);

            // Update the stock by adding the input quantity
            $product->stock += $quantity; // Increase the stock
            $product->save(); // Save the updated product

            DB::commit();

            return response()->json([
                'message' => 'Product stock updated successfully.',
                'product' => $product,
            ], StatusCode::OK->value);
        } catch (ModelNotFoundException $e) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['message' => $e->getMessage()], StatusCode::NOT_FOUND->value);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['message' => 'An error occurred while updating the stock.'], StatusCode::INTERNAL_SERVER_ERROR->value);
        }
    }
}
