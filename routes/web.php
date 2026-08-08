<?php

use App\Http\Controllers\Admin\AdminDashboardPageController;
use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\Auth\WebRegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebLoginController::class, 'showLoginForm'])
        ->name('login');
    Route::post('/login', [WebLoginController::class, 'login']);

    Route::get('/register', [WebRegisterController::class, 'showRegistrationForm'])
        ->name('register');
    Route::post('/register', [WebRegisterController::class, 'register']);
});

Route::post('/logout', [WebLoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/admin/dashboard', [AdminDashboardPageController::class, 'index'])
    ->middleware('auth')
    ->name('admin.dashboard');
