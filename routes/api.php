<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\OCApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/purchase-orders', [OCApi::class, 'getAllCompaniesOCs']);
Route::get('/ocs', [OCApi::class, 'showOrder']);
Route::get('/companies', [CompanyController::class, 'show']);
