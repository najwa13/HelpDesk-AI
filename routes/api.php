<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResource('tickets', TicketController::class)
            ->only(['index', 'store', 'show']);
        Route::patch('/tickets/{ticket}/statut', [
            TicketController::class,
            'updateStatus',
        ]);

        Route::patch('/tickets/{ticket}/affecter', [
            TicketController::class,
            'assign',
        ]);
        Route::get('/tickets/{ticket}/messages', [
            MessageController::class,
            'index',
        ]);

        Route::post('/tickets/{ticket}/messages', [
            MessageController::class,
            'store',
        ]);
        Route::apiResource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });
});
