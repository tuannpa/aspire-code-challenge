<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Registration and Authentication API
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->prefix('v1')->group(function () {
    // Customer API
    Route::get('customer-loans', [CustomerController::class, 'getCustomerLoans']);
    Route::get('customer-payments', [CustomerController::class, 'getCustomerPayments']);
    Route::apiResource('customer', CustomerController::class);

    // Product API
    Route::apiResource('product', ProductController::class);

    // Loan API
    Route::apiResource('loan', LoanController::class);

    // Payment API
    Route::apiResource('payment', PaymentController::class);
});
