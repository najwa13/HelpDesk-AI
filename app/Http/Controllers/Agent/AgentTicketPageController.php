<?php

namespace App\Http\Controllers\Agent;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\SearchLog;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentTicketPageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Agent) {
            abort(403);
        }

        $query = Ticket::query()
            ->where('agent_id', $user->id)
            ->with(['client:id,name', 'categorie:id,nom']);

        // Apply filters
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('categorie', fn ($q) => $q->where('nom', 'like', "%{$search}%"));
            });
        }

        $tickets = $query->latest('id')
            ->paginate(15)
            ->withQueryString();

        $ticketsData = $tickets->getCollection()->map(function (Ticket $ticket) {
            return [
                'id' => $ticket->id,
                'titre' => $ticket->titre,
                'description' => $ticket->description,
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

        // Compute counts for filter badges (without search filter)
        $countQuery = Ticket::query()->where('agent_id', $user->id);
        $allTickets = (clone $countQuery)->get();
        $counts = [
            'Tous' => $allTickets->count(),
            'Ouvert' => $allTickets->where('statut', 'ouvert')->count(),
            'En cours' => $allTickets->where('statut', 'en_cours')->count(),
            'Résolu' => $allTickets->where('statut', 'resolu')->count(),
            'Fermé' => $allTickets->where('statut', 'ferme')->count(),
        ];

        $filterMap = ['Tous' => '', 'Ouvert' => 'ouvert', 'En cours' => 'en_cours', 'Résolu' => 'resolu', 'Fermé' => 'ferme'];
        $currentStatus = $request->get('statut', '');
        $activeFilter = 'Tous';
        foreach ($filterMap as $label => $val) {
            if ($val === $currentStatus) {
                $activeFilter = $label;
                break;
            }
        }

        return view('agent.tickets.index', [
            'tickets' => $ticketsData,
            'pagination' => $tickets,
            'counts' => $counts,
            'filterMap' => $filterMap,
            'activeFilter' => $activeFilter,
            'currentSearch' => $request->get('search', ''),
        ]);
    }

    public function show(Ticket $ticket)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Agent || $ticket->agent_id !== $user->id) {
            abort(403);
        }

        $ticket->load([
            'client:id,name,email',
            'categorie:id,nom',
            'messages' => function ($query) {
                $query->with('auteur:id,name,role')
                    ->oldest('id');
            },
            'suggestionsIA' => function ($query) {
                $query->latest('id')
                    ->limit(1);
            },
        ]);

        $transitions = array_map(
            fn (TicketStatus $statut) => $statut->value,
            $ticket->statut->transitionsValides()
        );

        $statutsDisponibles = collect(TicketStatus::cases())
            ->map(fn (TicketStatus $statut) => [
                'value' => $statut->value,
                'label' => match ($statut) {
                    TicketStatus::Ouvert => 'Ouvert',
                    TicketStatus::EnCours => 'En cours',
                    TicketStatus::Resolu => 'Résolu',
                    TicketStatus::Ferme => 'Fermé',
                },
            ])
            ->all();

        $articleLie = SearchLog::query()
            ->where('ticket_id', $ticket->id)
            ->whereNotNull('article_id')
            ->latest('id')
            ->first();

        $ticketData = [
            'id' => $ticket->id,
            'titre' => $ticket->titre,
            'description' => $ticket->description,
            'statut' => $ticket->statut->value,
            'transitions' => $transitions,
            'statuts_disponibles' => $statutsDisponibles,
            'article_lie' => $articleLie
                ? 'Article #KB-'.str_pad((string) $articleLie->article_id, 2, '0', STR_PAD_LEFT).' lié'
                : null,
            'priorite' => $ticket->priorite?->value,
            'client' => $ticket->client
                ? [
                    'id' => $ticket->client->id,
                    'name' => $ticket->client->name,
                    'email' => $ticket->client->email,
                ]
                : null,
            'categorie' => $ticket->categorie
                ? ['id' => $ticket->categorie->id, 'nom' => $ticket->categorie->nom]
                : null,
            'messages' => $ticket->messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'contenu' => $message->contenu,
                    'auteur' => $message->auteur
                        ? [
                            'id' => $message->auteur->id,
                            'name' => $message->auteur->name,
                            'role' => $message->auteur->role?->value,
                        ]
                        : null,
                    'created_at' => $message->created_at?->toISOString(),
                ];
            })->all(),
            'suggestion_ia' => $ticket->suggestionsIA->first()
                ? [
                    'id' => $ticket->suggestionsIA->first()->id,
                    'resume' => $ticket->suggestionsIA->first()->resume,
                    'categorie_proposee' => $ticket->suggestionsIA->first()->categorie_proposee,
                    'priorite_proposee' => $ticket->suggestionsIA->first()->priorite_proposee,
                    'brouillon_reponse' => $ticket->suggestionsIA->first()->brouillon_reponse,
                    'statut' => $ticket->suggestionsIA->first()->statut,
                    'created_at' => $ticket->suggestionsIA->first()->created_at?->toISOString(),
                    'updated_at' => $ticket->suggestionsIA->first()->updated_at?->toISOString(),
                ]
                : null,
            'created_at' => $ticket->created_at?->toISOString(),
            'updated_at' => $ticket->updated_at?->toISOString(),
        ];

        return view('agent.tickets.show', compact('ticketData'));
    }
}
