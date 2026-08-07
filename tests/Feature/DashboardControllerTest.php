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
    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $this->agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $this->otherAgent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $this->client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $this->category = Category::create([
        'nom' => 'Technique',
        'description' => 'Support technique',
    ]);
});

test('un admin peut consulter les statistiques globales', function () {
    Sanctum::actingAs($this->admin);

    Ticket::create([
        'titre' => 'Ticket test',
        'description' => 'Description test',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->agent->id,
        'categorie_id' => $this->category->id,
    ]);

    $response = $this->getJson('/api/v1/dashboard/admin');

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'stats' => [
                    'tickets_this_month' => [
                        'value',
                        'trend_percentage',
                    ],
                    'kb_resolution_rate' => [
                        'value',
                        'unit',
                    ],
                    'resolved_tickets' => [
                        'value',
                    ],
                    'ai_analyses' => [
                        'value',
                    ],
                ],
                'tickets_by_month',
                'category_distribution',
                'tickets_by_category_over_time',
                'recent_tickets',
            ],
        ])
        ->assertJsonPath(
            'data.stats.tickets_this_month.value',
            1
        );
});

test('les statistiques admin comptent les tickets résolus', function () {
    Sanctum::actingAs($this->admin);

    Ticket::create([
        'titre' => 'Ticket résolu',
        'description' => 'Description',
        'statut' => TicketStatus::Resolu,
        'client_id' => $this->client->id,
        'agent_id' => $this->agent->id,
        'categorie_id' => $this->category->id,
    ]);

    $this->getJson('/api/v1/dashboard/admin')
        ->assertOk()
        ->assertJsonPath(
            'data.stats.resolved_tickets.value',
            1
        );
});

test('un agent ne peut pas consulter le dashboard admin', function () {
    Sanctum::actingAs($this->agent);

    $this->getJson('/api/v1/dashboard/admin')
        ->assertForbidden();
});

test('un client ne peut pas consulter le dashboard admin', function () {
    Sanctum::actingAs($this->client);

    $this->getJson('/api/v1/dashboard/admin')
        ->assertForbidden();
});

test('un agent peut consulter ses statistiques', function () {
    Sanctum::actingAs($this->agent);

    Ticket::create([
        'titre' => 'Ticket ouvert',
        'description' => 'Description',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->agent->id,
        'categorie_id' => $this->category->id,
    ]);

    Ticket::create([
        'titre' => 'Ticket en cours',
        'description' => 'Description',
        'statut' => TicketStatus::EnCours,
        'client_id' => $this->client->id,
        'agent_id' => $this->agent->id,
        'categorie_id' => $this->category->id,
    ]);

    $response = $this->getJson('/api/v1/dashboard/agent');

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.stats.open_tickets.value',
            1
        )
        ->assertJsonPath(
            'data.stats.in_progress_tickets.value',
            1
        )
        ->assertJsonCount(
            2,
            'data.recent_tickets'
        );
});

test('le dashboard agent ne contient que ses tickets', function () {
    Sanctum::actingAs($this->agent);

    Ticket::create([
        'titre' => 'Mon ticket',
        'description' => 'Description',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->agent->id,
        'categorie_id' => $this->category->id,
    ]);

    Ticket::create([
        'titre' => 'Ticket autre agent',
        'description' => 'Description',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->otherAgent->id,
        'categorie_id' => $this->category->id,
    ]);

    $response = $this->getJson('/api/v1/dashboard/agent');

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.stats.open_tickets.value',
            1
        )
        ->assertJsonCount(
            1,
            'data.recent_tickets'
        )
        ->assertJsonPath(
            'data.recent_tickets.0.titre',
            'Mon ticket'
        );
});

test('un admin ne peut pas consulter le dashboard agent', function () {
    Sanctum::actingAs($this->admin);

    $this->getJson('/api/v1/dashboard/agent')
        ->assertForbidden();
});

test('un client ne peut pas consulter le dashboard agent', function () {
    Sanctum::actingAs($this->client);

    $this->getJson('/api/v1/dashboard/agent')
        ->assertForbidden();
});

test('un utilisateur non authentifie ne peut pas consulter les dashboards', function () {
    $this->getJson('/api/v1/dashboard/admin')
        ->assertUnauthorized();

    $this->getJson('/api/v1/dashboard/agent')
        ->assertUnauthorized();
});
