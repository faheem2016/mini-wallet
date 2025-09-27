<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/transactions', [\App\Http\Controllers\Api\TransactionController::class, 'index']);
    Route::post('/transactions', [\App\Http\Controllers\Api\TransactionController::class, 'store']);
});
