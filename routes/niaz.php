<?php

use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:api', 'role:admin'])->group(function () {

    //Admin promo-codes Section
    Route::get('/promo-codes', [PromoCodeController::class, 'index']);
    Route::post('/promo-codes-store', [PromoCodeController::class, 'store']);
    Route::get('/promo-codes/{id}', [PromoCodeController::class, 'show']);
    Route::put('/promo-codes-update/{id}', [PromoCodeController::class, 'update']);
    Route::patch('/promo-codes/{id}/status', [PromoCodeController::class, 'updateStatus']);
    Route::delete('/promo-codes/{id}', [PromoCodeController::class, 'destroy']);

    //Admin order Section
    Route::get('/admin/orders', [OrderController::class, 'getAdminOrders']);
    Route::get('/admin/orders/{id}', [OrderController::class, 'getOrderDetail']);
});

Route::post('/payment/process', [PaymentController::class, 'processPayment'])->name('payment.process');

Route::get('/stripe-key', function () {
    return response()->json(['key' => config('services.stripe.key')]);
});
