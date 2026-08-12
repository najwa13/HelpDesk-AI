<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);
});

function creerConversationClient(User $participant, array $overrides = []): string
{
    $conversationId = (string) Str::uuid();

    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'participant_type' => User::class,
        'participant_id' => $participant->id,
        'title' => 'Conversation client',
        'created_at' => now(),
        'updated_at' => now(),
        ...$overrides,
    ]);

    return $conversationId;
}

test('un client sans conversation obtient available false', function () {
    Sanctum::actingAs($this->client);

    $this->getJson('/api/v1/client/ai/chat/latest')
        ->assertOk()
        ->assertJson([
            'available' => false,
            'conversation' => null,
        ]);
});

test('un client obtient sa conversation la plus récente', function () {
    Sanctum::actingAs($this->client);

    creerConversationClient($this->client, [
        'title' => 'Ancienne conversation',
        'updated_at' => now()->subDay(),
    ]);

    $latestId = creerConversationClient($this->client, [
        'title' => 'Conversation récente',
        'updated_at' => now(),
    ]);

    $this->getJson('/api/v1/client/ai/chat/latest')
        ->assertOk()
        ->assertJsonPath('available', true)
        ->assertJsonPath('conversation.id', $latestId)
        ->assertJsonPath('conversation.title', 'Conversation récente');
});

test('le endpoint recent ne mélange pas les conversations entre deux clients', function () {
    Sanctum::actingAs($this->client);

    creerConversationClient($this->otherClient, [
        'title' => 'Conversation de l autre client',
        'updated_at' => now(),
    ]);

    $this->getJson('/api/v1/client/ai/chat/latest')
        ->assertOk()
        ->assertJsonPath('available', false);
});

test('un agent ne peut pas consulter la dernière conversation client', function () {
    Sanctum::actingAs($this->agent);

    $this->getJson('/api/v1/client/ai/chat/latest')->assertForbidden();
});

test('un admin ne peut pas consulter la dernière conversation client', function () {
    Sanctum::actingAs($this->admin);

    $this->getJson('/api/v1/client/ai/chat/latest')->assertForbidden();
});

test('la conversation d un autre client est inaccessible via latest', function () {
    Sanctum::actingAs($this->client);

    creerConversationClient($this->otherClient, [
        'title' => 'Conversation privée',
        'updated_at' => now(),
    ]);

    $this->getJson('/api/v1/client/ai/chat/latest')
        ->assertOk()
        ->assertJsonPath('available', false);
});

test('la route latest ne collisionne pas avec la route dynamique', function () {
    Sanctum::actingAs($this->client);

    $conversationId = creerConversationClient($this->client);

    $this->getJson('/api/v1/client/ai/chat/latest')
        ->assertOk()
        ->assertJsonPath('conversation.id', $conversationId);

    $this->getJson("/api/v1/client/ai/chat/{$conversationId}")
        ->assertOk()
        ->assertJsonPath('data.conversation_id', $conversationId);
});
