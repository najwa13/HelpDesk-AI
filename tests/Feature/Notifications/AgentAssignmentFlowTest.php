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

    $this->agentA = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $this->agentB = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $this->ticket = Ticket::create([
        'titre' => 'Problème de connexion',
        'description' => 'Impossible de se connecter.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'categorie_id' => $this->category->id,
    ]);
});

test('admin assigne un ticket non assigné à un agent : notification créée', function () {
    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => $this->agentA->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'agent_id' => $this->agentA->id,
    ]);

    expect($this->agentA->notifications()->count())->toBe(1);
});

test('admin réassigne A vers B : notification créée pour B uniquement', function () {
    $this->ticket->update(['agent_id' => $this->agentA->id]);

    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => $this->agentB->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'agent_id' => $this->agentB->id,
    ]);

    expect($this->agentA->notifications()->count())->toBe(0);
    expect($this->agentB->notifications()->count())->toBe(1);
});

test('admin assigne A vers A : aucune notification dupliquée', function () {
    $this->ticket->update(['agent_id' => $this->agentA->id]);

    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => $this->agentA->id,
    ])->assertRedirect();

    expect($this->agentA->notifications()->count())->toBe(0);
});

test('admin retire un agent : agent_id null sans notification', function () {
    $this->ticket->update(['agent_id' => $this->agentA->id]);

    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => null,
    ])->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'agent_id' => null,
    ]);

    expect($this->agentA->notifications()->count())->toBe(0);
});

test('un admin ne peut pas être assigné comme agent', function () {
    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => $this->admin->id,
    ])->assertRedirect()
        ->assertSessionHasErrors('agent_id');

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'agent_id' => null,
    ]);
});

test('un client ne peut pas être assigné comme agent', function () {
    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => $this->client->id,
    ])->assertRedirect()
        ->assertSessionHasErrors('agent_id');

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'agent_id' => null,
    ]);
});

test('un agent voit ses notifications dans le dropdown', function () {
    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => $this->agentA->id,
    ]);

    $this->actingAs($this->agentA);

    $this->get('/agent/tickets')
        ->assertOk()
        ->assertSee('Nouveau ticket assigné')
        ->assertSee('Le ticket #TK-'.str_pad((string) $this->ticket->id, 4, '0', STR_PAD_LEFT).' vous a été assigné.');
});

test('un agent ne voit pas les notifications d un autre agent', function () {
    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => $this->agentB->id,
    ]);

    $this->actingAs($this->agentA);

    $this->get('/agent/tickets')
        ->assertOk()
        ->assertDontSee('Nouveau ticket assigné');
});

test('cliquer une notification ouvre le ticket et marque comme lue', function () {
    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => $this->agentA->id,
    ]);

    $notification = $this->agentA->notifications()->first();

    $this->actingAs($this->agentA);

    $this->get("/agent/notifications/{$notification->id}/open")
        ->assertRedirect("/agent/tickets/{$this->ticket->id}");

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('un agent ne peut pas ouvrir une notification d un autre agent', function () {
    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => $this->agentB->id,
    ]);

    $notification = $this->agentB->notifications()->first();

    $this->actingAs($this->agentA);

    $this->get("/agent/notifications/{$notification->id}/open")
        ->assertForbidden();
});

test('tout marquer comme lu met à jour les notifications', function () {
    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => $this->agentA->id,
    ]);

    $this->actingAs($this->agentA);

    $this->get('/agent/tickets')
        ->assertOk()
        ->assertSee('Nouveau ticket assigné');

    $this->post('/agent/notifications/read-all')
        ->assertRedirect();

    expect($this->agentA->unreadNotifications()->count())->toBe(0);
});
