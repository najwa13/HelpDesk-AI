<?php

namespace App\Http\Controllers\Agent;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class AgentDashboardPageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Agent) {
            abort(403);
        }

        $assignedTickets = Ticket::query()
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
                    'updated_at' => $ticket->updated_at?->toISOString(),
                ];
            });

        $ticketsOuverts = $assignedTickets->where('statut', 'ouvert')->count();
        $ticketsEnCours = $assignedTickets->where('statut', 'en_cours')->count();
        $ticketsResolus = $assignedTickets->where('statut', 'resolu')->count();
        $ticketsFermes = $assignedTickets->where('statut', 'ferme')->count();

        $stats = [
            'assigned_count' => $assignedTickets->count(),
            'tickets_ouverts' => $ticketsOuverts,
            'tickets_en_cours' => $ticketsEnCours,
            'tickets_resolus' => $ticketsResolus,
            'tickets_fermes' => $ticketsFermes,
        ];

        $recentTickets = $assignedTickets->take(10)->values()->all();

        return view('agent.dashboard', compact(
            'stats',
            'recentTickets'
        ));
    }
}
