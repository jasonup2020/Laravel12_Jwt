<?php

use App\Http\Controllers\AuthController;

Route::controller(AuthController::class)->group(function () {
    Route::prefix('authentications')->group(function () {
        Route::post('', 'login')->middleware(['throttle:20,2', 'auth.client']);
        Route::delete('', 'logout')->middleware('auth.client.jwt');
        Route::get('', 'check')->middleware('auth.client.jwt');
    });
});
