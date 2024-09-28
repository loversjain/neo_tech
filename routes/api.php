<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Admin\AdminController;

// Public authentication routes
Route::post('login', [AuthController::class, 'login']);

// Protected routes that require authentication
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh-token', [AuthController::class, 'refreshToken']); 
    
    // User routes for order management
    Route::controller(UserController::class)
        ->middleware(['user'])
        ->prefix('orders')
        ->as('orders.')
        ->group(function () {
            Route::post('', 'createOrder')->name('create');     // POST /orders
            Route::patch('{id}', 'updateOrder')->name('update');  // PUT /orders/{id}
            Route::delete('{id}', 'deleteOrder')->name('delete'); // DELETE /orders/{id}
            Route::get('', 'viewOrders')->name('index');        // GET /orders
        });
    
    // Admin routes for user and order management
    Route::middleware(['admin'])->group(function () {
        Route::controller(AdminController::class)
            ->prefix('admin')
            ->as('admin.')
            ->group(function () {
                // User management routes
                Route::prefix('users')->as('users.')->group(function () {
                    Route::patch('{id}/status', 'manageUser')->name('manageUser'); // PUT /admin/users/{id}/status
                });
                
                // Order management routes
                Route::prefix('orders')->as('orders.')->group(function () {
                    Route::get('', 'allOrders')->name('index');          // GET /admin/orders
                    Route::get('{id}', 'viewOrder')->name('show');       // GET /admin/orders/{id}
                });
                
                // Product management routes
                Route::prefix('products')->as('products.')->group(function () {
                    // Route to get a product by ID
                    Route::get('{id}',  'showProduct')->name('showProduct'); // GET /admin/products/{id}
                    // Route to update product stock
                    Route::patch('{id}/stock', 'updateProductStock')->name('updateProductStock'); // PUT /admin/products/{id}/stock
                });
            });
        });
});
