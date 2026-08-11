<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignTicketRequest;
use App\Models\SearchLog;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class AdminTicketPageController extends Controller
{
    public function __construct(private TicketService $ticketService) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $query = Ticket::query()
            ->with(['client:id,name', 'agent:id,name', 'categorie:id,nom']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('agent', fn ($q) => $q->where('name', 'like', "%{$search}%"))
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
                'agent' => $ticket->agent
                    ? ['id' => $ticket->agent->id, 'name' => $ticket->agent->name]
                    : null,
                'categorie' => $ticket->categorie
                    ? ['id' => $ticket->categorie->id, 'nom' => $ticket->categorie->nom]
                    : null,
                'created_at' => $ticket->created_at?->toISOString(),
            ];
        });

        $allTickets = Ticket::query()->get();
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

        return view('admin.tickets.index', [
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

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $ticket->load([
            'client:id,name,email',
            'agent:id,name,email',
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

        $agents = User::query()
            ->where('role', UserRole::Agent)
            ->orderBy('name')
            ->get(['id', 'name']);

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
            'priorite' => $ticket->priorite?->value,
            'client' => $ticket->client
                ? ['id' => $ticket->client->id, 'name' => $ticket->client->name, 'email' => $ticket->client->email]
                : null,
            'agent' => $ticket->agent
                ? ['id' => $ticket->agent->id, 'name' => $ticket->agent->name]
                : null,
            'categorie' => $ticket->categorie
                ? ['id' => $ticket->categorie->id, 'nom' => $ticket->categorie->nom]
                : null,
            'article_lie' => $articleLie
                ? 'Article #KB-'.str_pad((string) $articleLie->article_id, 2, '0', STR_PAD_LEFT).' lié'
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
                ]
                : null,
            'created_at' => $ticket->created_at?->toISOString(),
            'updated_at' => $ticket->updated_at?->toISOString(),
        ];

        return view('admin.tickets.show', [
            'ticketData' => $ticketData,
            'agents' => $agents,
        ]);
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        try {
            $result = $this->ticketService->assign(
                $ticket,
                $request->validated('agent_id'),
                $user
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors([
                'agent_id' => $e->getMessage(),
            ]);
        }

        if ($result['agent_id'] === null) {
            return back()->with('success', 'Ticket retiré de l\'agent. Le ticket n\'est plus assigné.');
        }

        if (! $result['notified']) {
            return back()->with('success', 'L\'agent est déjà assigné à ce ticket.');
        }

        return back()->with('success', 'Ticket assigné à '.$ticket->fresh()->agent?->name.'.');
    }
}
