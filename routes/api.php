<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\OCApi;
use App\Http\Controllers\Api\OrdersApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/purchase-orders', [OrdersApi::class, 'getOrders']);
Route::post('/details-order', [OrdersApi::class, 'getOrder']);
Route::post('/mark-as-read', [OrdersApi::class, 'markAsRead']);
Route::post('/handle-approval', [OrdersApi::class, 'handleApproval']);
Route::get('/ocs', [OCApi::class, 'showOrder']);
Route::get('/companies', [CompanyController::class, 'show']);
