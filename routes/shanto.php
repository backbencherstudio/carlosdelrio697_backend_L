<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServiceSubmitController;



Route::middleware(['auth:api', 'role:admin'])->group(function () {

    Route::get('/service-forms', [ServiceController::class, 'index']);
    Route::post('/service-form', [ServiceController::class, 'store']);
    Route::get('/service-form-edit/{service}', [ServiceController::class, 'edit']);
    Route::post('/service-form-update/{service}', [ServiceController::class, 'update']);
    Route::delete('/service-form-delete/{service}', [ServiceController::class, 'destroy']);

    //form submit
    Route::post('/service-store/{service}', [ServiceSubmitController::class, 'submit']);

});

