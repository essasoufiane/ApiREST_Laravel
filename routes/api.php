<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\FriendshipController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/users', [UserController::class, 'store']);

Route::post('/login', [UserController::class, 'login']);

Route::put('/users/{id}', [UserController::class, 'update'])->middleware('auth:sanctum');

Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('auth:sanctum');

Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/friends/request/{friendId}', [FriendshipController::class, 'sendRequest'])->middleware('auth:sanctum');

Route::post('/friends/accept/{friendId}', [FriendshipController::class, 'acceptRequest'])->middleware('auth:sanctum');

Route::get('/friends', [FriendshipController::class, 'getFriends'])->middleware('auth:sanctum');

Route::get('/users/search', [FriendshipController::class, 'searchUsers'])->middleware('auth:sanctum');

Route::get('/friends/requests/pending', [FriendshipController::class, 'getPendingFriendRequests'])->middleware('auth:sanctum');
