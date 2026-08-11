<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Jobs\ProcessAgentAiChatJob;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

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

    $this->otherAgent = User::factory()->create([
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

test('un agent affecté peut envoyer un message au chat IA', function () {
    Bus::fake();

    Sanctum::actingAs($this->agent);

    $response = $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat",
        [
            'message' => 'Explique-moi les causes possibles.',
        ]
    );

    $response
        ->assertAccepted()
        ->assertJsonPath(
            'message',
            'Message envoyé à l’assistant IA.'
        );

    Bus::assertDispatched(
        ProcessAgentAiChatJob::class,
        function (ProcessAgentAiChatJob $job) {
            return $job->ticket->id === $this->ticket->id
                && $job->message === 'Explique-moi les causes possibles.'
                && $job->conversationId === null;
        }
    );
});

test('un agent peut continuer une conversation IA existante', function () {
    Bus::fake();

    Sanctum::actingAs($this->agent);

    $conversationId = (string) Str::uuid();

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat",
        [
            'message' => 'Donne-moi trois questions à poser au client.',
            'conversation_id' => $conversationId,
        ]
    )->assertAccepted();

    Bus::assertDispatched(
        ProcessAgentAiChatJob::class,
        function (ProcessAgentAiChatJob $job) use ($conversationId) {
            return $job->ticket->id === $this->ticket->id
                && $job->conversationId === $conversationId
                && $job->message === 'Donne-moi trois questions à poser au client.';
        }
    );
});

test('un agent non affecté ne peut pas utiliser le chat IA', function () {
    Bus::fake();

    Sanctum::actingAs($this->otherAgent);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat",
        [
            'message' => 'Analyse ce ticket.',
        ]
    )->assertForbidden();

    Bus::assertNotDispatched(ProcessAgentAiChatJob::class);
});

test('un client ne peut pas utiliser le chat IA', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat",
        [
            'message' => 'Analyse ce ticket.',
        ]
    )->assertForbidden();

    Bus::assertNotDispatched(ProcessAgentAiChatJob::class);
});

test('le message du chat IA est obligatoire', function () {
    Bus::fake();

    Sanctum::actingAs($this->agent);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat",
        []
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('message');

    Bus::assertNotDispatched(ProcessAgentAiChatJob::class);
});

test('un agent affecté peut consulter historique de sa conversation IA', function () {
    Sanctum::actingAs($this->agent);

    $conversationId = (string) Str::uuid();

    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'participant_type' => Ticket::class,
        'participant_id' => $this->ticket->id,
        'title' => 'Diagnostic imprimante',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('agent_conversation_messages')->insert([
        [
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversationId,
            'participant_type' => Ticket::class,
            'participant_id' => $this->ticket->id,
            'agent' => 'App\\Ai\\Agents\\AgentSupportAssistant',
            'role' => 'user',
            'content' => 'Explique le problème.',
            'attachments' => '[]',
            'tool_calls' => '[]',
            'tool_results' => '[]',
            'usage' => '[]',
            'meta' => '[]',
            'approval_state' => null,
            'created_at' => now()->subSecond(),
            'updated_at' => now()->subSecond(),
        ],
        [
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversationId,
            'participant_type' => Ticket::class,
            'participant_id' => $this->ticket->id,
            'agent' => 'App\\Ai\\Agents\\AgentSupportAssistant',
            'role' => 'assistant',
            'content' => 'Voici les causes possibles.',
            'attachments' => '[]',
            'tool_calls' => '[]',
            'tool_results' => '[]',
            'usage' => '[]',
            'meta' => '[]',
            'approval_state' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat/{$conversationId}"
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.conversation_id',
            $conversationId
        )
        ->assertJsonPath(
            'data.title',
            'Diagnostic imprimante'
        )
        ->assertJsonCount(2, 'data.messages')
        ->assertJsonPath(
            'data.messages.0.role',
            'user'
        )
        ->assertJsonPath(
            'data.messages.0.content',
            'Explique le problème.'
        )
        ->assertJsonPath(
            'data.messages.1.role',
            'assistant'
        );
});

test('un agent non affecté ne peut pas consulter historique IA', function () {
    Sanctum::actingAs($this->otherAgent);

    $conversationId = (string) Str::uuid();

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat/{$conversationId}"
    )->assertForbidden();
});

test('une conversation inexistante retourne 404', function () {
    Sanctum::actingAs($this->agent);

    $conversationId = '00000000-0000-0000-0000-000000000000';

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat/{$conversationId}"
    )
        ->assertNotFound()
        ->assertJsonPath(
            'message',
            'Conversation IA introuvable pour ce ticket.'
        );
});

test('une conversation appartenant à un autre ticket retourne 404', function () {
    Sanctum::actingAs($this->agent);

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

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat/{$conversationId}"
    )
        ->assertNotFound()
        ->assertJsonPath(
            'message',
            'Conversation IA introuvable pour ce ticket.'
        );
});

test('latest retourne available false quand aucune conversation', function () {
    Sanctum::actingAs($this->agent);

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat/latest"
    )
        ->assertOk()
        ->assertJsonPath('available', false)
        ->assertJsonPath('conversation', null);
});

test('latest retourne la dernière conversation du ticket', function () {
    Sanctum::actingAs($this->agent);

    $conversationId = (string) Str::uuid();

    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'participant_type' => Ticket::class,
        'participant_id' => $this->ticket->id,
        'title' => 'Diagnostic réseau',
        'created_at' => now()->subHour(),
        'updated_at' => now(),
    ]);

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat/latest"
    )
        ->assertOk()
        ->assertJsonPath('available', true)
        ->assertJsonPath('conversation.id', $conversationId)
        ->assertJsonPath('conversation.title', 'Diagnostic réseau');
});

test('latest ne retourne pas les conversations d\'un autre ticket', function () {
    Sanctum::actingAs($this->agent);

    $otherTicket = Ticket::create([
        'titre' => 'Autre ticket',
        'description' => 'Description.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->agent->id,
        'categorie_id' => $this->category->id,
    ]);

    DB::table('agent_conversations')->insert([
        'id' => (string) Str::uuid(),
        'participant_type' => Ticket::class,
        'participant_id' => $otherTicket->id,
        'title' => 'Conv autre ticket',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat/latest"
    )
        ->assertOk()
        ->assertJsonPath('available', false);
});

test('un agent non affecté ne peut pas appeler latest', function () {
    Sanctum::actingAs($this->otherAgent);

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat/latest"
    )->assertForbidden();
});

test('latest retourne la conversation la plus récente', function () {
    Sanctum::actingAs($this->agent);

    $oldId = (string) Str::uuid();
    $newId = (string) Str::uuid();

    DB::table('agent_conversations')->insert([
        'id' => $oldId,
        'participant_type' => Ticket::class,
        'participant_id' => $this->ticket->id,
        'title' => 'Ancienne conv',
        'created_at' => now()->subDays(3),
        'updated_at' => now()->subDays(2),
    ]);

    DB::table('agent_conversations')->insert([
        'id' => $newId,
        'participant_type' => Ticket::class,
        'participant_id' => $this->ticket->id,
        'title' => 'Nouvelle conv',
        'created_at' => now()->subHour(),
        'updated_at' => now(),
    ]);

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat/latest"
    )
        ->assertOk()
        ->assertJsonPath('available', true)
        ->assertJsonPath('conversation.id', $newId)
        ->assertJsonPath('conversation.title', 'Nouvelle conv');
});

test('un message normal de trois questions n\'est jamais rejeté comme trop long', function () {
    Bus::fake();

    Sanctum::actingAs($this->agent);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat",
        [
            'message' => 'Donne-moi maintenant trois questions précises à poser au client.',
        ]
    )
        ->assertAccepted()
        ->assertJsonPath('message', 'Message envoyé à l’assistant IA.');

    Bus::assertDispatched(
        ProcessAgentAiChatJob::class,
        function (ProcessAgentAiChatJob $job) {
            return $job->message === 'Donne-moi maintenant trois questions précises à poser au client.';
        }
    );
});

test('un message réellement trop long est rejeté avec un message de validation en français', function () {
    Bus::fake();

    Sanctum::actingAs($this->agent);

    $response = $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/chat",
        [
            'message' => str_repeat('a', 2001),
        ]
    );

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('message');

    $this->assertSame(
        'Votre message dépasse la limite autorisée.',
        $response->json('errors.message.0')
    );

    Bus::assertNotDispatched(ProcessAgentAiChatJob::class);
});
