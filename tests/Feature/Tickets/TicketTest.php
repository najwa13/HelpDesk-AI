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
});

test('un administrateur peut affecter un ticket à un agent', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $ticket = Ticket::create([
        'titre' => 'Problème de connexion',
        'description' => 'Impossible de se connecter.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $client->id,
        'categorie_id' => $this->category->id,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->patchJson(
        "/api/v1/tickets/{$ticket->id}/affecter",
        [
            'agent_id' => $agent->id,
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.agent', $agent->name);

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'agent_id' => $agent->id,
    ]);
});

test('un client ne peut pas affecter un ticket à un agent', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $ticket = Ticket::create([
        'titre' => 'Problème technique',
        'description' => 'Description du problème.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $client->id,
        'categorie_id' => $this->category->id,
    ]);

    Sanctum::actingAs($client);

    $this->patchJson(
        "/api/v1/tickets/{$ticket->id}/affecter",
        [
            'agent_id' => $agent->id,
        ]
    )->assertForbidden();
});

test('un agent ne peut pas affecter un ticket', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $autreAgent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $ticket = Ticket::create([
        'titre' => 'Problème technique',
        'description' => 'Description du problème.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $client->id,
        'categorie_id' => $this->category->id,
    ]);

    Sanctum::actingAs($agent);

    $this->patchJson(
        "/api/v1/tickets/{$ticket->id}/affecter",
        [
            'agent_id' => $autreAgent->id,
        ]
    )->assertForbidden();
});

test('un agent voit uniquement les tickets qui lui sont affectés', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $autreAgent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $ticketAffecte = Ticket::create([
        'titre' => 'Ticket affecté',
        'description' => 'Ticket de cet agent.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $client->id,
        'agent_id' => $agent->id,
        'categorie_id' => $this->category->id,
    ]);

    $ticketAutreAgent = Ticket::create([
        'titre' => 'Ticket autre agent',
        'description' => 'Ticket affecté à un autre agent.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $client->id,
        'agent_id' => $autreAgent->id,
        'categorie_id' => $this->category->id,
    ]);

    Ticket::create([
        'titre' => 'Ticket non affecté',
        'description' => 'Ticket sans agent.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $client->id,
        'categorie_id' => $this->category->id,
    ]);

    Sanctum::actingAs($agent);

    $response = $this->getJson('/api/v1/tickets');

    $response
        ->assertOk()
        ->assertJsonFragment([
            'id' => $ticketAffecte->id,
            'titre' => 'Ticket affecté',
        ])
        ->assertJsonMissing([
            'id' => $ticketAutreAgent->id,
            'titre' => 'Ticket autre agent',
        ]);

    expect($response->json('data'))->toHaveCount(1);
});

test('un agent affecté peut passer un ticket de ouvert à en cours', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $ticket = Ticket::create([
        'titre' => 'Ticket à traiter',
        'description' => 'Description.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $client->id,
        'agent_id' => $agent->id,
        'categorie_id' => $this->category->id,
    ]);

    Sanctum::actingAs($agent);

    $response = $this->patchJson(
        "/api/v1/tickets/{$ticket->id}/statut",
        [
            'statut' => TicketStatus::EnCours->value,
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.statut',
            TicketStatus::EnCours->value
        );

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'statut' => TicketStatus::EnCours->value,
    ]);
});

test('un agent non affecté ne peut pas modifier le statut du ticket', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $agentAffecte = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $autreAgent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $ticket = Ticket::create([
        'titre' => 'Ticket affecté',
        'description' => 'Description.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $client->id,
        'agent_id' => $agentAffecte->id,
        'categorie_id' => $this->category->id,
    ]);

    Sanctum::actingAs($autreAgent);

    $this->patchJson(
        "/api/v1/tickets/{$ticket->id}/statut",
        [
            'statut' => TicketStatus::EnCours->value,
        ]
    )->assertForbidden();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'statut' => TicketStatus::Ouvert->value,
    ]);
});

test('une transition directe de ouvert à ferme est refusée', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $ticket = Ticket::create([
        'titre' => 'Ticket ouvert',
        'description' => 'Description.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $client->id,
        'agent_id' => $agent->id,
        'categorie_id' => $this->category->id,
    ]);

    Sanctum::actingAs($agent);

    $response = $this->patchJson(
        "/api/v1/tickets/{$ticket->id}/statut",
        [
            'statut' => TicketStatus::Ferme->value,
        ]
    );

    $response
        ->assertUnprocessable()
        ->assertJsonPath(
            'message',
            'Transition de statut invalide.'
        );

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'statut' => TicketStatus::Ouvert->value,
    ]);
});

test('un client ne peut pas modifier le statut de son ticket', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $ticket = Ticket::create([
        'titre' => 'Ticket client',
        'description' => 'Description.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $client->id,
        'agent_id' => $agent->id,
        'categorie_id' => $this->category->id,
    ]);

    Sanctum::actingAs($client);

    $this->patchJson(
        "/api/v1/tickets/{$ticket->id}/statut",
        [
            'statut' => TicketStatus::EnCours->value,
        ]
    )->assertForbidden();
});
