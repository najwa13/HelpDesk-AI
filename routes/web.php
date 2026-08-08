<?php

use App\Http\Controllers\Admin\AdminDashboardPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/dashboard', [AdminDashboardPageController::class, 'index'])
    ->middleware('auth')
    ->name('admin.dashboard');
