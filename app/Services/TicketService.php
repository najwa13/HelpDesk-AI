<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

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
}
