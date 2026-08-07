<?php

namespace App\Jobs;

use App\Ai\Agents\ClientSupportAssistant;
use App\Models\User;
use App\Services\KbSearchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Models\Conversation;
use RuntimeException;

class ProcessClientAiChatJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public User $client,
        public string $message,
        public ?string $conversationId = null,
    ) {}

    public function handle(KbSearchService $kbSearchService): void
    {
        /*
         * On réutilise exactement le moteur de recherche de la
         * Knowledge Base déjà développé.
         *
         * Il :
         * - normalise la requête ;
         * - cherche uniquement les articles publiés ;
         * - crée le SearchLog ;
         * - lance la détection de langue.
         */
        $searchLog = $kbSearchService->search(
            $this->message,
            $this->client
        );

        $assistant = new ClientSupportAssistant(
            $searchLog->article
        );

        if ($this->conversationId !== null) {
            $this->verifierConversation();

            $assistant->continue(
                $this->conversationId,
                $this->client
            );
        } else {
            $assistant->forUser($this->client);
        }

        $assistant->prompt(
            $this->message,
            provider: 'groq'
        );
    }

    private function verifierConversation(): void
    {
        $table = config(
            'ai.conversations.tables.conversations',
            'agent_conversations'
        );

        $exists = DB::table($table)
            ->where('id', $this->conversationId)
            ->where(
                'participant_type',
                Conversation::participantType($this->client)
            )
            ->where(
                'participant_id',
                Conversation::participantKey($this->client)
            )
            ->exists();

        if (! $exists) {
            throw new RuntimeException(
                'La conversation IA n’appartient pas à ce client.'
            );
        }
    }
}
