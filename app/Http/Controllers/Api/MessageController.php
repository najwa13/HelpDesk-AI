<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Ticket;
use App\Notifications\NewMessageNotification;

class MessageController extends Controller
{
    public function index(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $messages = $ticket->messages()
            ->with('auteur')
            ->oldest()
            ->get();

        return MessageResource::collection($messages);
    }

    public function store(StoreMessageRequest $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $message = $ticket->messages()->create([
            'contenu' => $request->validated('contenu'),
            'auteur_id' => $request->user()->id,
        ]);
        if ($request->user()->role === UserRole::Agent) {
            $ticket->client->notify(
                new NewMessageNotification($message)
            );
        }

        return new MessageResource(
            $message->load('auteur')
        );
    }
}
