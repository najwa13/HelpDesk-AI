<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
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
        $tickets = Ticket::with(['categorie', 'client', 'agent'])
            ->when(
                $request->user()->role === UserRole::Client,
                fn ($query) => $query->where('client_id', $request->user()->id)
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
                'messages.auteur',
            ])
        );
    }
}
