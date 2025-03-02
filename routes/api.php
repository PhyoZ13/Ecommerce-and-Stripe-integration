<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook']);

Route::middleware('customer-auth')->group(function () {

    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('orders', OrderController::class);

    Route::patch('/categories/{id}/restore', [CategoryController::class, 'restore']);
    Route::delete('/categories/{id}/force-delete', [CategoryController::class, 'forceDelete']);

    Route::patch('/products/{id}/restore', [ProductController::class, 'restore']);
    Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete']);

    Route::patch('/orders/{id}/restore', [OrderController::class, 'restore']);
    Route::delete('/orders/{id}/force-delete', [OrderController::class, 'forceDelete']);

    Route::get('/cart', [CartController::class, 'index']); // View Cart
    Route::post('/cart', [CartController::class, 'addToCart']); // Add to Cart
    Route::put('/cart/{id}', [CartController::class, 'updateCart']); // Update Cart
    Route::delete('/cart/{id}', [CartController::class, 'removeFromCart']); // Remove from Cart

    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'orderHistory']);

    Route::post('/payment/checkout', [PaymentController::class, 'createCheckoutSession']);
    Route::get('/payment/success', [PaymentController::class, 'paymentSuccess']);
    Route::get('/payment/cancel', [PaymentController::class, 'paymentCancel']);
});
