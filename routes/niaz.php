<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:api', 'role:admin'])->group(function () {

    //Admin Dashboard
    Route::get('admin-dashboard/metrics', [DashboardController::class, 'getMetrics']);
    Route::get('admin-dashboard/revenue-trend', [DashboardController::class, 'getRevenueTrend']);
    Route::get('admin-dashboard/revenue-by-service', [DashboardController::class, 'getRevenueByService']);
    Route::get('admin-dashboard/latest-orders', [DashboardController::class, 'latestOrders']);

    //Admin order Section
    Route::get('/admin/orders', [OrderController::class, 'getAdminOrders']);
    Route::get('/admin/order-details/{id}', [OrderController::class, 'getOrderDetail']);

    //customers Section
    Route::get('admin/customers', [CustomerController::class, 'index']);

    //Admin promo-codes Section
    Route::get('/promo-codes', [PromoCodeController::class, 'index']);
    Route::post('/promo-codes-store', [PromoCodeController::class, 'store']);
    Route::get('/promo-codes/{id}', [PromoCodeController::class, 'show']);
    Route::put('/promo-codes-update/{id}', [PromoCodeController::class, 'update']);
    Route::patch('/promo-codes/{id}/status', [PromoCodeController::class, 'updateStatus']);
    Route::delete('/promo-codes/{id}', [PromoCodeController::class, 'destroy']);
});

Route::post('/payment/process', [PaymentController::class, 'processPayment'])->name('payment.process');

Route::get('/stripe-key', function () {
    return response()->json(['key' => config('services.stripe.key')]);
});
