<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MailUserController AS ApiMailUserController;

Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Welcome~',
            ], 200);
})->middleware('auth.client.jwt');

// 获得MailConf
Route::get('email/mail_conf/get_conf', [ApiMailUserController::class, 'get_conf']);

Route::post('job/getEmailUser', [ApiMailUserController::class, 'getEmailUser']);

Route::post('job/getEmailUserJ_C', [ApiMailUserController::class, 'getEmailUserJ_C']);
Route::post('job/getEmailUserC_J', [ApiMailUserController::class, 'getEmailUserC_J']);




// Auth Routes
require_once __DIR__ . '/api/auth.php';

