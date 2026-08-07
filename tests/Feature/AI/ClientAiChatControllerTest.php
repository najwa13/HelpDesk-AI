<?php

use App\Enums\UserRole;
use App\Jobs\ProcessClientAiChatJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $this->otherClient = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $this->agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);
});

test('un client peut envoyer un message au chat IA', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $response = $this->postJson('/api/v1/client/ai/chat', [
        'message' => 'Comment réinitialiser mon mot de passe ?',
    ]);

    $response
        ->assertAccepted()
        ->assertJsonPath(
            'message',
            'Message envoyé à l’assistant IA.'
        );

    Bus::assertDispatched(
        ProcessClientAiChatJob::class,
        function (ProcessClientAiChatJob $job) {
            return $job->client->id === $this->client->id
                && $job->message === 'Comment réinitialiser mon mot de passe ?'
                && $job->conversationId === null;
        }
    );
});

test('un client peut continuer une conversation existante', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $conversationId = (string) Str::uuid();

    $this->postJson('/api/v1/client/ai/chat', [
        'message' => 'Peux-tu préciser ?',
        'conversation_id' => $conversationId,
    ])->assertAccepted();

    Bus::assertDispatched(
        ProcessClientAiChatJob::class,
        function (ProcessClientAiChatJob $job) use ($conversationId) {
            return $job->client->id === $this->client->id
                && $job->message === 'Peux-tu préciser ?'
                && $job->conversationId === $conversationId;
        }
    );
});

test('un agent ne peut pas utiliser le chat IA client', function () {
    Bus::fake();

    Sanctum::actingAs($this->agent);

    $this->postJson('/api/v1/client/ai/chat', [
        'message' => 'Bonjour',
    ])->assertForbidden();

    Bus::assertNotDispatched(ProcessClientAiChatJob::class);
});

test('le message est obligatoire', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $this->postJson('/api/v1/client/ai/chat', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('message');

    Bus::assertNotDispatched(ProcessClientAiChatJob::class);
});

test('un client peut consulter son historique IA', function () {
    Sanctum::actingAs($this->client);

    $conversationId = (string) Str::uuid();

    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'participant_type' => User::class,
        'participant_id' => $this->client->id,
        'title' => 'Réinitialisation mot de passe',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('agent_conversation_messages')->insert([
        [
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversationId,
            'participant_type' => User::class,
            'participant_id' => $this->client->id,
            'agent' => 'App\\Ai\\Agents\\ClientSupportAssistant',
            'role' => 'user',
            'content' => 'Comment réinitialiser mon mot de passe ?',
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
            'participant_type' => User::class,
            'participant_id' => $this->client->id,
            'agent' => 'App\\Ai\\Agents\\ClientSupportAssistant',
            'role' => 'assistant',
            'content' => 'Cliquez sur Mot de passe oublié.',
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
        "/api/v1/client/ai/chat/{$conversationId}"
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.conversation_id',
            $conversationId
        )
        ->assertJsonPath(
            'data.title',
            'Réinitialisation mot de passe'
        )
        ->assertJsonCount(2, 'data.messages')
        ->assertJsonPath(
            'data.messages.0.role',
            'user'
        )
        ->assertJsonPath(
            'data.messages.1.role',
            'assistant'
        );
});

test('un client ne peut pas consulter la conversation d un autre client', function () {
    Sanctum::actingAs($this->client);

    $conversationId = (string) Str::uuid();

    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'participant_type' => User::class,
        'participant_id' => $this->otherClient->id,
        'title' => 'Conversation privée',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->getJson(
        "/api/v1/client/ai/chat/{$conversationId}"
    )
        ->assertNotFound()
        ->assertJsonPath(
            'message',
            'Conversation IA introuvable pour ce client.'
        );
});

test('une conversation inexistante retourne 404', function () {
    Sanctum::actingAs($this->client);

    $conversationId = '00000000-0000-0000-0000-000000000000';

    $this->getJson(
        "/api/v1/client/ai/chat/{$conversationId}"
    )
        ->assertNotFound()
        ->assertJsonPath(
            'message',
            'Conversation IA introuvable pour ce client.'
        );
});

test('un agent ne peut pas consulter historique du chat client', function () {
    Sanctum::actingAs($this->agent);

    $conversationId = (string) Str::uuid();

    $this->getJson(
        "/api/v1/client/ai/chat/{$conversationId}"
    )->assertForbidden();
});
