<?php

use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:api', 'role:admin'])->group(function () {

    Route::get('/promo-codes', [PromoCodeController::class, 'index']);
    Route::post('/promo-codes-store', [PromoCodeController::class, 'store']);
    Route::get('/promo-codes/{id}', [PromoCodeController::class, 'show']);
    Route::put('/promo-codes-update/{id}', [PromoCodeController::class, 'update']);
    Route::patch('/promo-codes/{id}/status', [PromoCodeController::class, 'updateStatus']);
    Route::delete('/promo-codes/{id}', [PromoCodeController::class, 'destroy']);



    Route::post('/payment/process', [PaymentController::class, 'processPayment'])->name('payment.process');
});
