<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentAiChatRequest;
use App\Jobs\ProcessAgentAiChatJob;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AiChatController extends Controller
{
    public function send(
        AgentAiChatRequest $request,
        Ticket $ticket
    ): JsonResponse {
        $this->authorize('updateStatus', $ticket);

        ProcessAgentAiChatJob::dispatch(
            ticket: $ticket,
            message: $request->validated('message'),
            conversationId: $request->validated('conversation_id'),
        );

        return response()->json([
            'message' => 'Message envoyé à l’assistant IA.',
        ], 202);
    }

    public function history(Ticket $ticket, string $conversationId): JsonResponse
    {
        $this->authorize('view', $ticket);

        $conversationTable = config(
            'ai.conversations.tables.conversations',
            'agent_conversations'
        );

        $messagesTable = config(
            'ai.conversations.tables.messages',
            'agent_conversation_messages'
        );

        $conversation = DB::table($conversationTable)
            ->where('id', $conversationId)
            ->where('participant_type', Ticket::class)
            ->where('participant_id', $ticket->id)
            ->first();

        if (! $conversation) {
            return response()->json([
                'message' => 'Conversation IA introuvable pour ce ticket.',
            ], 404);
        }

        $messages = DB::table($messagesTable)
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->get([
                'id',
                'role',
                'content',
                'created_at',
            ]);

        return response()->json([
            'data' => [
                'conversation_id' => $conversation->id,
                'title' => $conversation->title,
                'messages' => $messages,
            ],
        ]);
    }
}
