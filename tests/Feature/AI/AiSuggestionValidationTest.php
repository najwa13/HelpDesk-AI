<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\AiSuggestion;
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

    $this->suggestion = AiSuggestion::create([
        'resume' => 'Problème avec une imprimante réseau.',
        'categorie_proposee' => 'Technique',
        'priorite_proposee' => 'moyenne',
        'brouillon_reponse' => 'Brouillon généré par IA.',
        'statut' => 'en_attente_validation',
        'ticket_id' => $this->ticket->id,
    ]);
});

test('un agent affecté peut modifier le brouillon IA', function () {
    Sanctum::actingAs($this->agent);

    $response = $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analysis",
        [
            'brouillon_reponse' => 'Brouillon corrigé et personnalisé par l’agent.',
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Brouillon IA modifié.')
        ->assertJsonPath(
            'data.brouillon_reponse',
            'Brouillon corrigé et personnalisé par l’agent.'
        )
        ->assertJsonPath(
            'data.statut',
            'en_attente_validation'
        );

    $this->assertDatabaseHas('ai_suggestions', [
        'id' => $this->suggestion->id,
        'brouillon_reponse' => 'Brouillon corrigé et personnalisé par l’agent.',
        'statut' => 'en_attente_validation',
    ]);
});

test('un agent affecté peut valider une suggestion IA', function () {
    Sanctum::actingAs($this->agent);

    $response = $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analysis/validate"
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'message',
            'Suggestion IA validée.'
        )
        ->assertJsonPath(
            'data.statut',
            'validee'
        );

    $this->assertDatabaseHas('ai_suggestions', [
        'id' => $this->suggestion->id,
        'statut' => 'validee',
    ]);
});

test('une suggestion validée ne peut plus être modifiée', function () {
    $this->suggestion->update([
        'statut' => 'validee',
    ]);

    Sanctum::actingAs($this->agent);

    $response = $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analysis",
        [
            'brouillon_reponse' => 'Modification interdite.',
        ]
    );

    $response
        ->assertUnprocessable()
        ->assertJsonPath(
            'message',
            'Cette suggestion IA a déjà été validée.'
        );

    expect(
        $this->suggestion->refresh()->brouillon_reponse
    )->toBe('Brouillon généré par IA.');
});

test('une suggestion ne peut pas être validée deux fois', function () {
    $this->suggestion->update([
        'statut' => 'validee',
    ]);

    Sanctum::actingAs($this->agent);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analysis/validate"
    )
        ->assertUnprocessable()
        ->assertJsonPath(
            'message',
            'Cette suggestion IA a déjà été validée.'
        );
});

test('un agent non affecté ne peut pas modifier la suggestion IA', function () {
    Sanctum::actingAs($this->otherAgent);

    $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analysis",
        [
            'brouillon_reponse' => 'Modification interdite.',
        ]
    )->assertForbidden();

    expect(
        $this->suggestion->refresh()->brouillon_reponse
    )->toBe('Brouillon généré par IA.');
});

test('un agent non affecté ne peut pas valider la suggestion IA', function () {
    Sanctum::actingAs($this->otherAgent);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analysis/validate"
    )->assertForbidden();

    expect(
        $this->suggestion->refresh()->statut
    )->toBe('en_attente_validation');
});

test('un client ne peut pas modifier la suggestion IA', function () {
    Sanctum::actingAs($this->client);

    $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analysis",
        [
            'brouillon_reponse' => 'Modification client interdite.',
        ]
    )->assertForbidden();
});

test('le brouillon est obligatoire lors de la modification', function () {
    Sanctum::actingAs($this->agent);

    $this->patchJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analysis",
        []
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('brouillon_reponse');
});
