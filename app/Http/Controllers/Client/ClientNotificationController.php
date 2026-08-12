<?php

namespace App\Http\Controllers\Client;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ClientNotificationController extends Controller
{
    public function open(DatabaseNotification $notification)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Client) {
            abort(403);
        }

        if ($notification->notifiable_id !== $user->id) {
            abort(403);
        }

        $notification->markAsRead();

        $ticketId = $notification->data['ticket_id'] ?? null;

        if ($ticketId && Route::has('client.tickets.show')) {
            return redirect()->route('client.tickets.show', $ticketId);
        }

        return redirect()->route('client.tickets.index');
    }

    public function readAll()
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Client) {
            abort(403);
        }

        $user->unreadNotifications->markAsRead();

        return back();
    }
}
