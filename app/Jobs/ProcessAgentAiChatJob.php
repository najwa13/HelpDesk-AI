<?php

namespace App\Jobs;

use App\Ai\Agents\AgentSupportAssistant;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Models\Conversation;
use RuntimeException;

class ProcessAgentAiChatJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public Ticket $ticket,
        public string $message,
        public ?string $conversationId = null,
    ) {}

    public function handle(): void
    {
        $assistant = new AgentSupportAssistant($this->ticket);

        if ($this->conversationId !== null) {
            $this->verifierConversation();

            $assistant->continue(
                $this->conversationId,
                $this->ticket
            );
        } else {
            $assistant->forParticipant($this->ticket);
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
                Conversation::participantType($this->ticket)
            )
            ->where(
                'participant_id',
                Conversation::participantKey($this->ticket)
            )
            ->exists();

        if (! $exists) {
            throw new RuntimeException(
                'La conversation IA n’appartient pas à ce ticket.'
            );
        }
    }
}
