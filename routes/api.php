<?php

use App\Http\Controllers\YukkPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/v1.0/generate-token', [YukkPaymentController::class, 'generateAccessToken'])->name('generate_token');
Route::get('/v1.0/generate-qr', [YukkPaymentController::class, 'generateQRIS'])->name('generate_qr');
Route::get('/v1.0/qr/qr-mpm-query', [YukkPaymentController::class, 'queryPayment'])->name('query_payment');
Route::get('/v1.0/qr/qr-mpm-query-payment-status', [YukkPaymentController::class, 'queryPaymentFromEmail'])->name('query_payment_email');
Route::get('/v1.0/access-token/b2b', [YukkPaymentController::class, 'generateAccessTokenForYUKK']);
Route::get('/v1.0/qr/qr-mpm-notify', [YukkPaymentController::class, 'paymentNotification'])->name('notify_payment');
