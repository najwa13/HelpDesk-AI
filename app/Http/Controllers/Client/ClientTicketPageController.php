<?php

namespace App\Http\Controllers\Client;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Category;
use App\Models\SearchLog;
use App\Models\Ticket;
use App\Notifications\AgentNewMessageNotification;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClientTicketPageController extends Controller
{
    public function __construct(private TicketService $ticketService) {}

    public function index(Request $request): View
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Client) {
            abort(403);
        }

        $query = Ticket::query()
            ->where('client_id', $user->id)
            ->with(['categorie:id,nom']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
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
                'categorie' => $ticket->categorie
                    ? ['id' => $ticket->categorie->id, 'nom' => $ticket->categorie->nom]
                    : null,
                'created_at' => $ticket->created_at?->toISOString(),
                'updated_at' => $ticket->updated_at?->toISOString(),
            ];
        });

        $allTickets = Ticket::query()->where('client_id', $user->id)->get();
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

        $openCount = $allTickets->whereIn('statut', ['ouvert', 'en_cours'])->count();
        $solvedCount = $allTickets->where('statut', 'resolu')->count();
        $closedCount = $allTickets->where('statut', 'ferme')->count();

        return view('client.tickets.index', [
            'tickets' => $ticketsData,
            'pagination' => $tickets,
            'counts' => $counts,
            'filterMap' => $filterMap,
            'activeFilter' => $activeFilter,
            'currentSearch' => $request->get('search', ''),
            'stats' => [
                ['label' => 'Demandes en cours', 'value' => (string) $openCount],
                ['label' => 'Résolues', 'value' => (string) $solvedCount],
                ['label' => 'Fermées', 'value' => (string) $closedCount],
            ],
        ]);
    }

    public function create(): View
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Client) {
            abort(403);
        }

        $categories = Category::orderBy('nom')->get(['id', 'nom']);

        return view('client.tickets.create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Client) {
            abort(403);
        }

        $ticket = $this->ticketService->create(
            $request->validated(),
            $user
        );

        return redirect()
            ->route('client.tickets.show', $ticket)
            ->with('success', 'Votre demande a bien été créée.');
    }

    public function show(Ticket $ticket): View
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Client || $ticket->client_id !== $user->id) {
            abort(403);
        }

        $ticket->load([
            'categorie:id,nom',
            'messages' => function ($query) {
                $query->with('auteur:id,name,role')
                    ->oldest('id');
            },
        ]);

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
            'article_lie' => $articleLie
                ? 'Article #KB-'.str_pad((string) $articleLie->article_id, 2, '0', STR_PAD_LEFT)
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
            'created_at' => $ticket->created_at?->toISOString(),
            'updated_at' => $ticket->updated_at?->toISOString(),
        ];

        return view('client.tickets.show', compact('ticketData'));
    }

    public function message(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Client || $ticket->client_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ]);

        $message = $ticket->messages()->create([
            'auteur_id' => $user->id,
            'contenu' => $validated['contenu'],
        ]);

        if ($ticket->agent_id !== null) {
            $message->load('auteur');
            $ticket->agent->notify(new AgentNewMessageNotification($message));
        }

        return back()->with('success', 'Votre message a bien été envoyé.');
    }
}
