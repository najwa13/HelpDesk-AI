<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use App\Notifications\TicketAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

test('agent reçoit une notification lorsqu un ticket lui est affecté', function () {
    Notification::fake();

    Sanctum::actingAs($this->admin);

    $response = $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/affecter",
        [
            'agent_id' => $this->agent->id,
        ]
    );

    $response->assertOk();

    Notification::assertSentTo(
        $this->agent,
        TicketAssignedNotification::class
    );
});

test('client reçoit une notification lorsqu un agent répond à son ticket', function () {
    Notification::fake();

    $this->ticket->update([
        'agent_id' => $this->agent->id,
    ]);

    Sanctum::actingAs($this->agent);

    $response = $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        [
            'contenu' => 'Bonjour, votre demande est en cours de traitement.',
        ]
    );

    $response->assertCreated();

    Notification::assertSentTo(
        $this->client,
        NewMessageNotification::class
    );
});

test('client ne reçoit pas une notification lorsqu il répond lui même', function () {
    Notification::fake();

    $this->ticket->update([
        'agent_id' => $this->agent->id,
    ]);

    Sanctum::actingAs($this->client);

    $response = $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        [
            'contenu' => 'Voici des informations supplémentaires.',
        ]
    );

    $response->assertCreated();

    Notification::assertNotSentTo(
        $this->client,
        NewMessageNotification::class
    );
});
test('la notification de nouveau message contient les bonnes données', function () {
    Notification::fake();

    $this->ticket->update([
        'agent_id' => $this->agent->id,
    ]);

    Sanctum::actingAs($this->agent);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/messages",
        [
            'contenu' => 'Bonjour, votre demande est en cours de traitement.',
        ]
    )->assertCreated();

    Notification::assertSentTo(
        $this->client,
        NewMessageNotification::class,
        function (NewMessageNotification $notification) {
            $data = $notification->toDatabase($this->client);

            return $data['ticket_id'] === $this->ticket->id
                && isset($data['message_id'])
                && $data['message']
                    === "Une nouvelle réponse a été ajoutée au ticket #{$this->ticket->id}.";
        }
    );
});

test('la notification d affectation contient les bonnes données', function () {
    Notification::fake();

    Sanctum::actingAs($this->admin);

    $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/affecter",
        [
            'agent_id' => $this->agent->id,
        ]
    )->assertOk();

    Notification::assertSentTo(
        $this->agent,
        TicketAssignedNotification::class,
        function (TicketAssignedNotification $notification) {
            $data = $notification->toDatabase($this->agent);

            return $data['ticket_id'] === $this->ticket->id
                && $data['message']
                    === "Le ticket #{$this->ticket->id} vous a été affecté.";
        }
    );
});
