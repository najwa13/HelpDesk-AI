<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(private TicketService $ticketService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $tickets = Ticket::with(['categorie', 'client', 'agent'])
            ->when(
                $user->role === UserRole::Client,
                fn ($query) => $query->where('client_id', $user->id)
            )
            ->when(
                $user->role === UserRole::Agent,
                fn ($query) => $query->where('agent_id', $user->id)
            )
            ->latest()
            ->paginate(15);

        return TicketResource::collection($tickets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->ticketService->create(
            $request->validated(),
            $request->user()
        );

        return new TicketResource(
            $ticket->load(['categorie', 'client'])
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return new TicketResource(
            $ticket->load([
                'categorie',
                'client',
                'agent',
            ])
        );
    }

    public function updateStatus(
        UpdateTicketStatusRequest $request,
        Ticket $ticket
    ) {
        $this->authorize('updateStatus', $ticket);

        $nouveauStatut = TicketStatus::from(
            $request->validated('statut')
        );

        $statutActuel = $ticket->statut;

        if (! in_array(
            $nouveauStatut,
            $statutActuel->transitionsValides(),
            true
        )) {
            return response()->json([
                'message' => 'Transition de statut invalide.',
            ], 422);
        }

        $ticket->update([
            'statut' => $nouveauStatut,
        ]);

        return new TicketResource(
            $ticket->load([
                'categorie',
                'client',
                'agent',
            ])
        );
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket)
    {
        $this->authorize('assign', Ticket::class);

        $agent = User::query()
            ->whereKey($request->validated('agent_id'))
            ->where('role', UserRole::Agent->value)
            ->first();

        if (! $agent) {
            return response()->json([
                'message' => 'L’utilisateur sélectionné n’est pas un agent.',
            ], 422);
        }

        $ticket->update([
            'agent_id' => $agent->id,
        ]);
        $agent->notify(
            new TicketAssignedNotification($ticket)
        );

        return new TicketResource(
            $ticket->load([
                'categorie',
                'client',
                'agent',
            ])
        );
    }
}
