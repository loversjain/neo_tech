<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Orders\OrderRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Enums\ResponseMessage;
use App\Enums\StatusCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\OrderResource;
use Auth;
use DB;
use Exception;
use Throwable;
use App\Models\Product;
use App\Models\Order;

class UserController extends Controller
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Create a new order.
     *
     * @param  OrderRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrder(OrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $product = $this->productRepository->find($request->input('product_id'));
            $this->checkStockAvailability($product, $request->input('quantity'));

            $totalPrice = $this->calculateTotalPrice($product, $request->input('quantity'));
            $order = $this->createNewOrder($request, $totalPrice);

            $this->adjustStockAfterOrderCreation($product, $request->input('quantity'));

            DB::commit();

            Log::info('Order created successfully.', ['order' => $order]);
            return response()->json(['data' => $order, 'message' => ResponseMessage::ORDER_CREATED->value], StatusCode::CREATED->value);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json(['message' => 'Order creation failed'], StatusCode::INTERNAL_SERVER_ERROR->value);
        }
    }

    /**
     * Update an order's quantity and adjust stock accordingly.
     *
     * @param  OrderRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function updateOrder(OrderRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $order = $this->orderRepository->getById($id);
            $this->authorizeUser($order->user_id);

            $product = $this->productRepository->find($order->product_id);
            $this->validateProduct($product);

            $newQuantity = $request->validated()['quantity'];
            $totalPrice = $this->calculateTotalPrice($product, $newQuantity);

            $this->adjustStockAfterOrderUpdate($product, $order->quantity, $newQuantity);

            $this->orderRepository->update($id, [
                'quantity' => $newQuantity,
                'total_price' => $totalPrice
            ]);

            DB::commit();

            Log::info('Order updated successfully.', ['order_id' => $id]);
            return response()->json([
                'message' => ResponseMessage::ORDER_UPDATED->value,
                'data' => ['order_id' => $id, 'quantity' => $newQuantity, 'total_price' => $totalPrice]
            ], StatusCode::OK->value);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Order not found for update.', ['order_id' => $id]);
            return response()->json(['message' => ResponseMessage::ORDER_NOT_FOUND->value], StatusCode::NOT_FOUND->value);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Order update failed: ' . $e->getMessage());
            return response()->json(['message' => 'Order update failed'], StatusCode::INTERNAL_SERVER_ERROR->value);
        }
    }

    /**
     * Delete an order and update the associated product stock.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function deleteOrder(int $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $order = $this->orderRepository->getById($id);
            $product = $order->product;

            if ($product) {
                $this->adjustStockAfterOrderDeletion($product, $order->quantity);
            }

            $this->orderRepository->delete($id);

            DB::commit();

            Log::info('Order deleted successfully.', ['order_id' => $id]);
            return response()->json(['message' => ResponseMessage::ORDER_DELETED->value], StatusCode::OK->value);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Order not found for deletion.', ['order_id' => $id]);
            return response()->json(['message' => ResponseMessage::ORDER_NOT_FOUND->value], StatusCode::NOT_FOUND->value);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Order deletion failed: ' . $e->getMessage());
            return response()->json(['message' => 'Order deletion failed'], StatusCode::INTERNAL_SERVER_ERROR->value);
        }
    }

    /**
     * View user orders.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function viewOrders(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $orders = $this->orderRepository->getUserOrders(auth()->id())->paginate($perPage);

            Log::info('Fetched orders successfully.', ['orders_count' => $orders->total()]);

            return OrderResource::collection($orders)->response()->setStatusCode(StatusCode::OK->value);
        } catch (Throwable $e) {
            Log::error('Failed to fetch orders: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch orders'], StatusCode::INTERNAL_SERVER_ERROR->value);
        }
    }

    /**
     * Helper to calculate total price.
     *
     * @param  Product  $product
     * @param  int  $quantity
     * @return float
     */
    protected function calculateTotalPrice(Product $product, int $quantity): float
    {
        return $product->price * $quantity;
    }

    /**
     * Helper to adjust stock after order creation.
     *
     * @param  Product  $product
     * @param  int  $quantity
     */
    protected function adjustStockAfterOrderCreation(Product $product, int $quantity): void
    {
         // Since it's a new order, old quantity is 0
        $this->adjustProductStock($product, 0, $quantity);
    }

    /**
     * Helper to adjust stock after order update.
     *
     * @param  Product  $product
     * @param  int  $oldQuantity
     * @param  int  $newQuantity
     */
    protected function adjustStockAfterOrderUpdate(Product $product, int $oldQuantity, int $newQuantity): void
    {
        $this->adjustProductStock($product, $oldQuantity, $newQuantity);
    }

    /**
     * Helper to adjust stock after order deletion.
     *
     * @param  Product  $product
     * @param  int  $orderQuantity
     */
    protected function adjustStockAfterOrderDeletion(Product $product, int $orderQuantity): void
    {
         // Since we're deleting, new quantity is 0
        $this->adjustProductStock($product, $orderQuantity, 0);
    }

    /**
     * Adjust the product's stock based on the change in quantity.
     *
     * @param  Product  $product
     * @param  int  $oldQuantity
     * @param  int  $newQuantity
     */
    protected function adjustProductStock(Product $product, int $oldQuantity, int $newQuantity): void
    {
        $quantityDifference = $newQuantity - $oldQuantity;

        if ($quantityDifference > 0) {
            $this->checkStockAvailability($product, $quantityDifference);
            $product->stock -= $quantityDifference;
        } else {
            $product->stock += abs($quantityDifference);
        }

        $product->save();
    }

    /**
     * Check stock availability for a product.
     *
     * @param  Product  $product
     * @param  int  $quantity
     * @throws Exception
     */
    protected function checkStockAvailability(Product $product, int $quantity): void
    {
        if ($product->stock < $quantity) {
            throw new Exception('Insufficient stock available.');
        }
    }

    /**
     * Authorize that the user can update the order.
     *
     * @param  int  $orderUserId
     * @throws Exception
     */
    protected function authorizeUser(int $orderUserId): void
    {
        if (Auth::id() !== $orderUserId) {
            throw new Exception('You are not authorized to update this order.');
        }
    }

    /**
     * Validate that the product exists.
     *
     * @param  Product|null  $product
     * @throws ModelNotFoundException
     */
    protected function validateProduct(?Product $product): void
    {
        if (!$product) {
            throw new ModelNotFoundException('Product not found.');
        }
    }

    /**
     * Create a new order.
     *
     * @param  OrderRequest  $request
     * @param  float  $totalPrice
     * @return Order
     */
    private function createNewOrder(OrderRequest $request, float $totalPrice): Order
    {
        $orderData = array_merge($request->validated(), [
            'total_price' => $totalPrice,
            'user_id' => auth()->id(),
        ]);

        return $this->orderRepository->create($orderData);
    }
}
