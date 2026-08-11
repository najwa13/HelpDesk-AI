<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $this->ticket = Ticket::create([
        'titre' => 'Problème réseau',
        'description' => 'Connexion réseau instable.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->agent->id,
        'categorie_id' => $this->category->id,
    ]);
});

test('un invité est redirigé vers le login depuis agent/dashboard', function () {
    $this->get('/agent/dashboard')
        ->assertRedirect('/login');
});

test('un agent peut accéder à son dashboard', function () {
    $this->actingAs($this->agent);

    $this->get('/agent/dashboard')
        ->assertOk()
        ->assertSee('Mon tableau de bord');
});

test('un admin ne peut pas accéder au dashboard agent', function () {
    $this->actingAs($this->admin);

    $this->get('/agent/dashboard')
        ->assertForbidden();
});

test('un client ne peut pas accéder au dashboard agent', function () {
    $this->actingAs($this->client);

    $this->get('/agent/dashboard')
        ->assertForbidden();
});

test('un agent peut lister ses tickets', function () {
    $this->actingAs($this->agent);

    $this->get('/agent/tickets')
        ->assertOk()
        ->assertSee('Mes tickets');
});

test('un agent ne voit que ses tickets assignés', function () {
    $otherTicket = Ticket::create([
        'titre' => 'Ticket autre agent',
        'description' => 'Ce ticket appartient à un autre agent.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->otherAgent->id,
        'categorie_id' => $this->category->id,
    ]);

    $this->actingAs($this->agent);

    $response = $this->get('/agent/tickets');
    $response->assertOk();
    $response->assertDontSee('Ticket autre agent');
});

test('un agent peut voir le détail de son ticket', function () {
    $this->actingAs($this->agent);

    $this->get("/agent/tickets/{$this->ticket->id}")
        ->assertOk()
        ->assertSee('Problème réseau');
});

test('un agent ne peut pas voir le détail d\'un ticket d\'un autre agent', function () {
    $otherTicket = Ticket::create([
        'titre' => 'Ticket confidentiel',
        'description' => 'Pas pour moi.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->otherAgent->id,
        'categorie_id' => $this->category->id,
    ]);

    $this->actingAs($this->agent);

    $this->get("/agent/tickets/{$otherTicket->id}")
        ->assertForbidden();
});

test('un admin ne peut pas accéder à la liste des tickets agent', function () {
    $this->actingAs($this->admin);

    $this->get('/agent/tickets')
        ->assertForbidden();
});

test('un admin ne peut pas voir le détail d\'un ticket agent', function () {
    $this->actingAs($this->admin);

    $this->get("/agent/tickets/{$this->ticket->id}")
        ->assertForbidden();
});
