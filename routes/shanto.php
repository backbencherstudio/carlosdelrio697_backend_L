<?php

use App\Http\Controllers\Admin\ServiceController;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:api', 'role:admin'])->group(function () {

    Route::post('/service-form', [ServiceController::class, 'store']);

});

