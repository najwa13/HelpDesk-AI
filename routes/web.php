<?php

use App\Http\Controllers\Admin\AdminDashboardPageController;
use App\Http\Controllers\Admin\AdminKnowledgePageController;
use App\Http\Controllers\Admin\AdminManagementPageController;
use App\Http\Controllers\Admin\AdminTicketPageController;
use App\Http\Controllers\Agent\AgentAssistantPageController;
use App\Http\Controllers\Agent\AgentDashboardPageController;
use App\Http\Controllers\Agent\AgentKnowledgePageController;
use App\Http\Controllers\Agent\AgentNotificationController;
use App\Http\Controllers\Agent\AgentTicketPageController;
use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\Auth\WebRegisterController;
use App\Http\Controllers\Client\ClientAssistantPageController;
use App\Http\Controllers\Client\ClientHelpPageController;
use App\Http\Controllers\Client\ClientNotificationController;
use App\Http\Controllers\Client\ClientTicketPageController;
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

Route::get('/admin/tickets', [AdminTicketPageController::class, 'index'])
    ->middleware('auth')
    ->name('admin.tickets.index');

Route::get('/admin/tickets/{ticket}', [AdminTicketPageController::class, 'show'])
    ->middleware('auth')
    ->name('admin.tickets.show');

Route::post('/admin/tickets/{ticket}/assign', [AdminTicketPageController::class, 'assign'])
    ->middleware('auth')
    ->name('admin.tickets.assign');

Route::get('/admin/management', [AdminManagementPageController::class, 'index'])
    ->middleware('auth')
    ->name('admin.management');

Route::post('/admin/management/categories', [AdminManagementPageController::class, 'storeCategory'])
    ->middleware('auth')
    ->name('admin.management.categories.store');

Route::delete('/admin/management/categories/{category}', [AdminManagementPageController::class, 'destroyCategory'])
    ->middleware('auth')
    ->name('admin.management.categories.destroy');

Route::get('/admin/knowledge', [AdminKnowledgePageController::class, 'index'])
    ->middleware('auth')
    ->name('admin.knowledge.index');

Route::get('/admin/knowledge/create', [AdminKnowledgePageController::class, 'create'])
    ->middleware('auth')
    ->name('admin.knowledge.create');

Route::post('/admin/knowledge', [AdminKnowledgePageController::class, 'store'])
    ->middleware('auth')
    ->name('admin.knowledge.store');

Route::get('/admin/knowledge/{article}', [AdminKnowledgePageController::class, 'show'])
    ->middleware('auth')
    ->name('admin.knowledge.show');

Route::get('/admin/knowledge/{article}/edit', [AdminKnowledgePageController::class, 'edit'])
    ->middleware('auth')
    ->name('admin.knowledge.edit');

Route::put('/admin/knowledge/{article}', [AdminKnowledgePageController::class, 'update'])
    ->middleware('auth')
    ->name('admin.knowledge.update');

Route::delete('/admin/knowledge/{article}', [AdminKnowledgePageController::class, 'destroy'])
    ->middleware('auth')
    ->name('admin.knowledge.destroy');

Route::get('/agent/dashboard', [AgentDashboardPageController::class, 'index'])
    ->middleware('auth')
    ->name('agent.dashboard');

Route::get('/agent/tickets', [AgentTicketPageController::class, 'index'])
    ->middleware('auth')
    ->name('agent.tickets.index');

Route::get('/agent/tickets/{ticket}', [AgentTicketPageController::class, 'show'])
    ->middleware('auth')
    ->name('agent.tickets.show');

Route::get('/agent/assistant', [AgentAssistantPageController::class, 'index'])
    ->middleware('auth')
    ->name('agent.assistant');

Route::get('/agent/knowledge', [AgentKnowledgePageController::class, 'index'])
    ->middleware('auth')
    ->name('agent.knowledge');

Route::get('/agent/knowledge/{article}', [AgentKnowledgePageController::class, 'show'])
    ->middleware('auth')
    ->name('agent.knowledge.show');

Route::get('/agent/notifications/{notification}/open', [AgentNotificationController::class, 'open'])
    ->middleware('auth')
    ->name('agent.notifications.open');

Route::post('/agent/notifications/read-all', [AgentNotificationController::class, 'readAll'])
    ->middleware('auth')
    ->name('agent.notifications.read-all');

Route::get('/client/tickets', [ClientTicketPageController::class, 'index'])
    ->middleware('auth')
    ->name('client.tickets.index');

Route::get('/client/tickets/create', [ClientTicketPageController::class, 'create'])
    ->middleware('auth')
    ->name('client.tickets.create');

Route::post('/client/tickets', [ClientTicketPageController::class, 'store'])
    ->middleware('auth')
    ->name('client.tickets.store');

Route::get('/client/tickets/{ticket}', [ClientTicketPageController::class, 'show'])
    ->middleware('auth')
    ->name('client.tickets.show');

Route::post('/client/tickets/{ticket}/messages', [ClientTicketPageController::class, 'message'])
    ->middleware('auth')
    ->name('client.tickets.message');

Route::get('/client/assistant', [ClientAssistantPageController::class, 'index'])
    ->middleware('auth')
    ->name('client.assistant');

Route::get('/client/help', [ClientHelpPageController::class, 'index'])
    ->middleware('auth')
    ->name('client.help');

Route::get('/client/help/{article}', [ClientHelpPageController::class, 'show'])
    ->middleware('auth')
    ->name('client.help.show');

Route::get('/client/notifications/{notification}/open', [ClientNotificationController::class, 'open'])
    ->middleware('auth')
    ->name('client.notifications.open');

Route::post('/client/notifications/read-all', [ClientNotificationController::class, 'readAll'])
    ->middleware('auth')
    ->name('client.notifications.read-all');
