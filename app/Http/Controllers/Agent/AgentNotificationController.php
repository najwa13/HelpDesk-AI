<?php

namespace App\Http\Controllers\Agent;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class AgentNotificationController extends Controller
{
    public function open(DatabaseNotification $notification)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Agent) {
            abort(403);
        }

        if ($notification->notifiable_id !== $user->id) {
            abort(403);
        }

        $notification->markAsRead();

        $url = $notification->data['url'] ?? route('agent.dashboard');

        return redirect($url);
    }

    public function readAll()
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Agent) {
            abort(403);
        }

        $user->unreadNotifications->markAsRead();

        return back();
    }
}
