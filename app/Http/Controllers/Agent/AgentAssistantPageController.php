<?php

namespace App\Http\Controllers\Agent;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class AgentAssistantPageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Agent) {
            abort(403);
        }

        $tickets = Ticket::query()
            ->where('agent_id', $user->id)
            ->with(['client:id,name', 'categorie:id,nom'])
            ->latest('id')
            ->get()
            ->map(function (Ticket $ticket) {
                return [
                    'id' => $ticket->id,
                    'titre' => $ticket->titre,
                    'statut' => $ticket->statut->value,
                    'priorite' => $ticket->priorite?->value,
                    'client' => $ticket->client
                        ? ['id' => $ticket->client->id, 'name' => $ticket->client->name]
                        : null,
                    'categorie' => $ticket->categorie
                        ? ['id' => $ticket->categorie->id, 'nom' => $ticket->categorie->nom]
                        : null,
                    'created_at' => $ticket->created_at?->toISOString(),
                ];
            });

        $selectedTicketId = request('ticket_id');

        return view('agent.assistant', [
            'tickets' => $tickets,
            'selectedTicketId' => $selectedTicketId,
        ]);
    }
}
