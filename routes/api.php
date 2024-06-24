<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/users', [UserController::class, 'store']);

Route::post('/login', [UserController::class, 'login']);

Route::put('/users/{id}', [UserController::class, 'update'])->middleware('auth:sanctum');

Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('auth:sanctum');
