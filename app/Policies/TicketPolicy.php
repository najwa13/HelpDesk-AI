<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        return match ($user->role) {
            UserRole::Admin => true,
            UserRole::Agent => $ticket->agent_id === $user->id,
            UserRole::Client => $ticket->client_id === $user->id,
        };
    }

    public function assign(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        return $user->role === UserRole::Agent
            && $ticket->agent_id === $user->id;
    }
}
