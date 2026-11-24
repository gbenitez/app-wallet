<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\TransferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/accounts/{account}', [AccountController::class, 'show']);
Route::get('/accounts/{account}/transfers', [AccountController::class, 'transfers']);
Route::post('/transfers', [TransferController::class, 'store']);
