<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\AgentNewMessageNotification;
use App\Notifications\NewMessageNotification;
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

    $this->otherAgent = User::factory()->create([
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

// ----- A. STATUTS -----

test('agent passe en_cours à resolu : 200 et DB mise à jour', function () {
    $this->ticket->update(['statut' => TicketStatus::EnCours]);
    Sanctum::actingAs($this->agent);

    $response = $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/statut",
        ['statut' => TicketStatus::Resolu->value]
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.statut', TicketStatus::Resolu->value);

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'statut' => TicketStatus::Resolu->value,
    ]);
});

test('agent passe resolu à ferme : 200 et DB mise à jour', function () {
    $this->ticket->update(['statut' => TicketStatus::Resolu]);
    Sanctum::actingAs($this->agent);

    $response = $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/statut",
        ['statut' => TicketStatus::Ferme->value]
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.statut', TicketStatus::Ferme->value);

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'statut' => TicketStatus::Ferme->value,
    ]);
});

test('transition invalide (en_cours → ferme) : 422', function () {
    $this->ticket->update(['statut' => TicketStatus::EnCours]);
    Sanctum::actingAs($this->agent);

    $response = $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/statut",
        ['statut' => TicketStatus::Ferme->value]
    );

    $response
        ->assertStatus(422)
        ->assertJsonPath('message', 'Transition de statut invalide.');

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'statut' => TicketStatus::EnCours->value,
    ]);
});

test('l UI du select n affiche que les statuts atteignables depuis en_cours', function () {
    $this->ticket->update(['statut' => TicketStatus::EnCours]);

    $this->actingAs($this->agent)
        ->get("/agent/tickets/{$this->ticket->id}")
        ->assertOk()
        ->assertSee('value="resolu"', false)
        ->assertDontSee('value="ouvert"', false)
        ->assertDontSee('value="ferme"', false);
});

test('l UI du select n affiche que les statuts atteignables depuis resolu', function () {
    $this->ticket->update(['statut' => TicketStatus::Resolu]);

    $this->actingAs($this->agent)
        ->get("/agent/tickets/{$this->ticket->id}")
        ->assertOk()
        ->assertSee('value="ferme"', false)
        ->assertDontSee('value="ouvert"', false);
});

test('l UI du select n affiche que en_cours depuis ouvert', function () {
    $this->actingAs($this->agent)
        ->get("/agent/tickets/{$this->ticket->id}")
        ->assertOk()
        ->assertSee('value="en_cours"', false)
        ->assertDontSee('value="resolu"', false)
        ->assertDontSee('value="ferme"', false);
});

test('workflow complet ouvert en_cours resolu ferme', function () {
    Sanctum::actingAs($this->agent);

    $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/statut",
        ['statut' => TicketStatus::EnCours->value]
    )->assertOk()
        ->assertJsonPath('data.statut', TicketStatus::EnCours->value);

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'statut' => TicketStatus::EnCours->value,
    ]);

    $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/statut",
        ['statut' => TicketStatus::Resolu->value]
    )->assertOk()
        ->assertJsonPath('data.statut', TicketStatus::Resolu->value);

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'statut' => TicketStatus::Resolu->value,
    ]);

    $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/statut",
        ['statut' => TicketStatus::Ferme->value]
    )->assertOk()
        ->assertJsonPath('data.statut', TicketStatus::Ferme->value);

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'statut' => TicketStatus::Ferme->value,
    ]);
});

test('agent non assigné ne peut pas changer le statut (403)', function () {
    Sanctum::actingAs($this->otherAgent);

    $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/statut",
        ['statut' => TicketStatus::EnCours->value]
    )->assertForbidden();

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'statut' => TicketStatus::Ouvert->value,
    ]);
});

// ----- B. NOTIFICATIONS -----

test('client envoie message sur ticket assigné : notification créée pour l Agent', function () {
    Sanctum::actingAs($this->client);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        ['contenu' => 'Le problème persiste.']
    )->assertCreated();

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $this->agent->id,
        'type' => AgentNewMessageNotification::class,
        'data->type' => 'new_message',
    ]);
});

test('client envoie message sur ticket non assigné : aucune notification Agent', function () {
    $this->ticket->update(['agent_id' => null]);
    Sanctum::actingAs($this->client);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        ['contenu' => 'Bonjour.']
    )->assertCreated();

    $this->assertDatabaseMissing('notifications', [
        'notifiable_id' => $this->agent->id,
        'type' => AgentNewMessageNotification::class,
    ]);
    $this->assertDatabaseMissing('notifications', [
        'notifiable_id' => $this->otherAgent->id,
        'type' => AgentNewMessageNotification::class,
    ]);
});

test('l Agent ne reçoit pas la notification d un autre ticket', function () {
    $otherTicket = Ticket::create([
        'titre' => 'Autre ticket',
        'description' => 'Desc.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->otherAgent->id,
        'categorie_id' => $this->category->id,
    ]);

    Sanctum::actingAs($this->client);

    $this->postJson(
        "/api/v1/tickets/{$otherTicket->id}/messages",
        ['contenu' => 'Sur l autre ticket.']
    )->assertCreated();

    $this->assertDatabaseMissing('notifications', [
        'notifiable_id' => $this->agent->id,
        'type' => AgentNewMessageNotification::class,
    ]);
    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $this->otherAgent->id,
        'type' => AgentNewMessageNotification::class,
    ]);
});

test('le badge unread augmente pour l Agent après un message client', function () {
    Sanctum::actingAs($this->client);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        ['contenu' => 'Hello.']
    )->assertCreated();

    $this->actingAs($this->agent);

    $response = $this->get('/agent/tickets');

    $response
        ->assertOk()
        ->assertSee('Nouveau message client')
        ->assertSee('#TK-'.str_pad((string) $this->ticket->id, 4, '0', STR_PAD_LEFT));

    $unread = $this->agent->unreadNotifications()->count();
    expect($unread)->toBe(1);
});

test('clic sur la notification ouvre le bon ticket et le marque comme lu', function () {
    Sanctum::actingAs($this->client);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        ['contenu' => 'Clic test.']
    )->assertCreated();

    $notification = $this->agent->notifications()->first();

    $this->actingAs($this->agent);

    $this->get("/agent/notifications/{$notification->id}/open")
        ->assertRedirect("/agent/tickets/{$this->ticket->id}");

    $this->assertNotNull($notification->fresh()->read_at);
});

test('web route client tmessage notifie l agent', function () {
    $this->actingAs($this->client);

    $this->post("/client/tickets/{$this->ticket->id}/messages", [
        'contenu' => 'Test via web form.',
    ])->assertRedirect();

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $this->agent->id,
        'type' => AgentNewMessageNotification::class,
    ]);
});

test('web route client tmessage sans agent : aucune notification', function () {
    $this->ticket->update(['agent_id' => null]);

    $this->actingAs($this->client);

    $this->post("/client/tickets/{$this->ticket->id}/messages", [
        'contenu' => 'Pas d agent.',
    ])->assertRedirect();

    $this->assertDatabaseMissing('notifications', [
        'notifiable_id' => $this->agent->id,
        'type' => AgentNewMessageNotification::class,
    ]);
});

test('agent repond au client via api : notification client', function () {
    Sanctum::actingAs($this->agent);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        ['contenu' => 'Reponse agent.']
    )->assertCreated();

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $this->client->id,
        'type' => NewMessageNotification::class,
    ]);
});
