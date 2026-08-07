<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientAiChatRequest;
use App\Jobs\ProcessClientAiChatJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientAiChatController extends Controller
{
    public function send(ClientAiChatRequest $request): JsonResponse
    {
        $client = $request->user();

        if ($client->role !== UserRole::Client) {
            abort(403);
        }

        ProcessClientAiChatJob::dispatch(
            client: $client,
            message: $request->validated('message'),
            conversationId: $request->validated('conversation_id'),
        );

        return response()->json([
            'message' => 'Message envoyé à l’assistant IA.',
        ], 202);
    }

    public function history(
        Request $request,
        string $conversationId
    ): JsonResponse {
        $client = $request->user();

        if ($client->role !== UserRole::Client) {
            abort(403);
        }

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
            ->where('participant_type', get_class($client))
            ->where('participant_id', $client->id)
            ->first();

        if (! $conversation) {
            return response()->json([
                'message' => 'Conversation IA introuvable pour ce client.',
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
