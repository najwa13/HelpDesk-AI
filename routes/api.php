<?php

use App\Http\Controllers\Api\AiAnalysisController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\KbSearchController;
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

        Route::apiResource('articles', ArticleController::class);

        Route::post('/kb/search', [
            KbSearchController::class,
            'search',
        ]);

        Route::post('/kb/search/{searchLogId}/ticket', [
            KbSearchController::class,
            'creerTicketDepuisRecherche',
        ]);
        Route::post('/tickets/{ticket}/ai/analyze', [
            AiAnalysisController::class,
            'analyze',
        ]);

        Route::get('/tickets/{ticket}/ai/analysis', [
            AiAnalysisController::class,
            'show',
        ]);

        Route::patch('/tickets/{ticket}/ai/analysis', [
            AiAnalysisController::class,
            'update',
        ]);

        Route::post('/tickets/{ticket}/ai/analysis/validate', [
            AiAnalysisController::class,
            'validateSuggestion',
        ]);
    });
});
