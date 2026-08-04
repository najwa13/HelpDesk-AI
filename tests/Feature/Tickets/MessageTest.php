<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    $this->ticket = Ticket::create([
        'titre' => 'Problème de connexion',
        'description' => 'Impossible de se connecter.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->agent->id,
        'categorie_id' => $this->category->id,
    ]);
});

test('le client propriétaire peut envoyer un message', function () {
    Sanctum::actingAs($this->client);

    $response = $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        [
            'contenu' => 'Bonjour, mon problème est toujours présent.',
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.contenu',
            'Bonjour, mon problème est toujours présent.'
        )
        ->assertJsonPath('data.auteur.id', $this->client->id)
        ->assertJsonPath('data.auteur.role', UserRole::Client->value);

    $this->assertDatabaseHas('messages', [
        'ticket_id' => $this->ticket->id,
        'auteur_id' => $this->client->id,
        'contenu' => 'Bonjour, mon problème est toujours présent.',
    ]);
});

test('agent affecté peut répondre au ticket', function () {
    Sanctum::actingAs($this->agent);

    $response = $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        [
            'contenu' => 'Bonjour, je prends en charge votre demande.',
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.contenu',
            'Bonjour, je prends en charge votre demande.'
        )
        ->assertJsonPath('data.auteur.id', $this->agent->id)
        ->assertJsonPath('data.auteur.role', UserRole::Agent->value);

    $this->assertDatabaseHas('messages', [
        'ticket_id' => $this->ticket->id,
        'auteur_id' => $this->agent->id,
        'contenu' => 'Bonjour, je prends en charge votre demande.',
    ]);
});

test('historique des messages est retourné dans ordre chronologique', function () {
    $this->ticket->messages()->create([
        'contenu' => 'Premier message',
        'auteur_id' => $this->client->id,
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    $this->ticket->messages()->create([
        'contenu' => 'Deuxième message',
        'auteur_id' => $this->agent->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Sanctum::actingAs($this->client);

    $response = $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/messages"
    );

    $response->assertOk();

    expect($response->json('data'))
        ->toHaveCount(2)
        ->and($response->json('data.0.contenu'))
        ->toBe('Premier message')
        ->and($response->json('data.1.contenu'))
        ->toBe('Deuxième message');
});

test('un agent non affecté ne peut pas lire les messages', function () {
    $autreAgent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    Sanctum::actingAs($autreAgent);

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/messages"
    )->assertForbidden();
});

test('un agent non affecté ne peut pas envoyer un message', function () {
    $autreAgent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    Sanctum::actingAs($autreAgent);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        [
            'contenu' => 'Je ne devrais pas pouvoir répondre.',
        ]
    )->assertForbidden();

    $this->assertDatabaseMissing('messages', [
        'ticket_id' => $this->ticket->id,
        'auteur_id' => $autreAgent->id,
    ]);
});

test('un autre client ne peut pas lire les messages du ticket', function () {
    $autreClient = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    Sanctum::actingAs($autreClient);

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/messages"
    )->assertForbidden();
});

test('un autre client ne peut pas envoyer un message sur le ticket', function () {
    $autreClient = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    Sanctum::actingAs($autreClient);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        [
            'contenu' => 'Message interdit.',
        ]
    )->assertForbidden();
});

test('le contenu du message est obligatoire', function () {
    Sanctum::actingAs($this->client);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        []
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('contenu');
});
