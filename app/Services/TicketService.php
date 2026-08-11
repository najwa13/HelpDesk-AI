<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use InvalidArgumentException;

class TicketService
{
    public function create(array $data, User $client, ?int $searchLogId = null): Ticket
    {
        return Ticket::create([
            'titre' => $data['titre'],
            'description' => $data['description'],
            'categorie_id' => $data['categorie_id'],
            'client_id' => $client->id,
            'statut' => TicketStatus::Ouvert,
        ]);
    }

    /**
     * Assign a ticket to an agent (or unassign with null).
     *
     * @return array{agent_id: int|null, notified: bool}
     *
     * @throws InvalidArgumentException when the given user is not an agent
     */
    public function assign(Ticket $ticket, ?int $agentId, ?User $assignedBy = null): array
    {
        if ($agentId === null) {
            $ticket->update(['agent_id' => null]);

            return ['agent_id' => null, 'notified' => false];
        }

        $agent = User::query()
            ->whereKey($agentId)
            ->where('role', UserRole::Agent->value)
            ->first();

        if (! $agent) {
            throw new InvalidArgumentException('L’utilisateur sélectionné n’est pas un agent.');
        }

        if ($ticket->agent_id === $agent->id) {
            return ['agent_id' => $agent->id, 'notified' => false];
        }

        $ticket->update(['agent_id' => $agent->id]);

        $agent->notify(new TicketAssignedNotification($ticket, $assignedBy));

        return ['agent_id' => $agent->id, 'notified' => true];
    }
}
