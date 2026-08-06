<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Jobs\AnalyzeTicketJob;
use App\Models\AiSuggestion;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
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
});

test('un agent affecté peut lancer une analyse IA', function () {
    Bus::fake();

    Sanctum::actingAs($this->agent);

    $response = $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analyze"
    );

    $response
        ->assertAccepted()
        ->assertJsonPath(
            'message',
            'Analyse IA lancée.'
        );

    Bus::assertDispatched(
        AnalyzeTicketJob::class,
        function (AnalyzeTicketJob $job) {
            return $job->ticket->id === $this->ticket->id;
        }
    );
});

test('un agent non affecté ne peut pas lancer une analyse IA', function () {
    Bus::fake();

    Sanctum::actingAs($this->otherAgent);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analyze"
    )->assertForbidden();

    Bus::assertNotDispatched(AnalyzeTicketJob::class);
});

test('un client ne peut pas lancer une analyse IA', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $this->postJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analyze"
    )->assertForbidden();

    Bus::assertNotDispatched(AnalyzeTicketJob::class);
});

test('un agent affecté peut consulter la dernière suggestion IA', function () {
    AiSuggestion::create([
        'resume' => 'Première analyse.',
        'categorie_proposee' => 'Technique',
        'priorite_proposee' => 'basse',
        'brouillon_reponse' => 'Premier brouillon.',
        'statut' => 'en_attente_validation',
        'ticket_id' => $this->ticket->id,
    ]);

    $derniereSuggestion = AiSuggestion::create([
        'resume' => 'Dernière analyse.',
        'categorie_proposee' => 'Technique',
        'priorite_proposee' => 'moyenne',
        'brouillon_reponse' => 'Dernier brouillon.',
        'statut' => 'en_attente_validation',
        'ticket_id' => $this->ticket->id,
    ]);

    Sanctum::actingAs($this->agent);

    $response = $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analysis"
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.id',
            $derniereSuggestion->id
        )
        ->assertJsonPath(
            'data.resume',
            'Dernière analyse.'
        )
        ->assertJsonPath(
            'data.priorite_proposee',
            'moyenne'
        )
        ->assertJsonPath(
            'data.brouillon_reponse',
            'Dernier brouillon.'
        )
        ->assertJsonPath(
            'data.statut',
            'en_attente_validation'
        );
});

test('un agent non affecté ne peut pas consulter analyse IA', function () {
    AiSuggestion::create([
        'resume' => 'Analyse.',
        'categorie_proposee' => 'Technique',
        'priorite_proposee' => 'moyenne',
        'brouillon_reponse' => 'Brouillon.',
        'statut' => 'en_attente_validation',
        'ticket_id' => $this->ticket->id,
    ]);

    Sanctum::actingAs($this->otherAgent);

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analysis"
    )->assertForbidden();
});

test('retourne 404 quand aucune analyse IA existe', function () {
    Sanctum::actingAs($this->agent);

    $this->getJson(
        "/api/v1/tickets/{$this->ticket->id}/ai/analysis"
    )
        ->assertNotFound()
        ->assertJsonPath(
            'message',
            'Aucune analyse IA disponible pour ce ticket.'
        );
});
