<?php

use App\Ai\Agents\AgentSupportAssistant;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Jobs\ProcessAgentAiChatJob;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::create([
        'nom' => 'Technique',
        'description' => 'Problèmes techniques',
    ]);

    $this->client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $this->agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $this->ticket = Ticket::create([
        'titre' => 'Problème imprimante réseau',
        'description' => 'Mon imprimante réseau ne fonctionne plus.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->agent->id,
        'categorie_id' => $this->category->id,
    ]);
});

test('le job peut démarrer une nouvelle conversation IA', function () {
    AgentSupportAssistant::fake([
        'Voici les causes possibles du problème.',
    ])->preventStrayPrompts();

    $job = new ProcessAgentAiChatJob(
        ticket: $this->ticket,
        message: 'Explique-moi les causes possibles.',
        conversationId: null,
    );

    $job->handle();

    expect(
        DB::table('agent_conversations')
            ->where('participant_type', Ticket::class)
            ->where('participant_id', $this->ticket->id)
            ->exists()
    )->toBeTrue();

    expect(
        DB::table('agent_conversation_messages')
            ->where('participant_type', Ticket::class)
            ->where('participant_id', $this->ticket->id)
            ->count()
    )->toBeGreaterThanOrEqual(2);
});

test('le job peut continuer une conversation appartenant au ticket', function () {
    AgentSupportAssistant::fake([
        'Voici trois questions précises à poser au client.',
    ])->preventStrayPrompts();

    $conversationId = (string) Str::uuid();

    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'participant_type' => Ticket::class,
        'participant_id' => $this->ticket->id,
        'title' => 'Diagnostic imprimante',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $job = new ProcessAgentAiChatJob(
        ticket: $this->ticket,
        message: 'Donne-moi trois questions à poser au client.',
        conversationId: $conversationId,
    );

    $job->handle();

    $messages = DB::table('agent_conversation_messages')
        ->where('conversation_id', $conversationId)
        ->orderBy('created_at')
        ->get();

    expect($messages)->not->toBeEmpty();

    expect(
        $messages->pluck('role')->all()
    )->toContain('user', 'assistant');
});

test('le job refuse une conversation appartenant à un autre ticket', function () {
    AgentSupportAssistant::fake()
        ->preventStrayPrompts();

    $otherTicket = Ticket::create([
        'titre' => 'Autre problème',
        'description' => 'Un autre ticket de support.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->agent->id,
        'categorie_id' => $this->category->id,
    ]);

    $conversationId = (string) Str::uuid();

    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'participant_type' => Ticket::class,
        'participant_id' => $otherTicket->id,
        'title' => 'Conversation autre ticket',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $job = new ProcessAgentAiChatJob(
        ticket: $this->ticket,
        message: 'Continue cette conversation.',
        conversationId: $conversationId,
    );

    expect(
        fn () => $job->handle()
    )->toThrow(
        RuntimeException::class,
        'La conversation IA n’appartient pas à ce ticket.'
    );
});

test('le job refuse une conversation inexistante', function () {
    AgentSupportAssistant::fake()
        ->preventStrayPrompts();

    $job = new ProcessAgentAiChatJob(
        ticket: $this->ticket,
        message: 'Continue cette conversation.',
        conversationId: '00000000-0000-0000-0000-000000000000',
    );

    expect(
        fn () => $job->handle()
    )->toThrow(
        RuntimeException::class,
        'La conversation IA n’appartient pas à ce ticket.'
    );
});
