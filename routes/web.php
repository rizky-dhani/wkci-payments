<?php

use App\Http\Controllers\YukkPaymentController;
use App\Livewire\Public\Homepage;
use App\Livewire\Public\Payments;
use Filament\Http\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
// Route::get('/', Login::class)->name('home');
// Route::get('/v1.0/generate-token', [YukkPaymentController::class, 'getAccessToken'])->name('generate_token');
// Route::get('/v1.0/generate-qr', [YukkPaymentController::class, 'generateQR'])->name('generate_qr');
// Route::get('/v1.0/generate-qr-manual', [YukkPaymentController::class, 'generateManualQR'])->name('generate_manual_qr');
// Route::get('/v1.0/qr/qr-mpm-query', [YukkPaymentController::class, 'queryPayment'])->name('query_payment');
// Route::get('/v1.0/qr/qr-mpm-query-payment-status', [YukkPaymentController::class, 'queryPaymentStatus'])->name('query_payment_status');
// Route::get('/v1.0/access-token/b2b', [YukkPaymentController::class, 'generateAccessTokenForYUKK']);
// Route::get('/v1.0/qr/qr-mpm-notify', [YukkPaymentController::class, 'paymentNotification'])->name('notify_payment');
Route::get('/payments', Payments::class)->name('payments');