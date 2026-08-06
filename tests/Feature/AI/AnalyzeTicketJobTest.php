<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Jobs\AnalyzeTicketJob;
use App\Models\AiSuggestion;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Services\GhostwriterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::create([
        'nom' => 'Technique',
        'description' => 'Problèmes techniques',
    ]);

    $this->client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $this->ticket = Ticket::create([
        'titre' => 'Problème imprimante réseau',
        'description' => 'Mon imprimante réseau ne fonctionne plus.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'categorie_id' => $this->category->id,
    ]);
});

test('le job crée une suggestion IA à partir du résultat du ghostwriter', function () {
    $this->mock(
        GhostwriterService::class,
        function (MockInterface $mock) {
            $mock->shouldReceive('analyser')
                ->once()
                ->withArgs(function (Ticket $ticket) {
                    return $ticket->id === $this->ticket->id;
                })
                ->andReturn([
                    'resume' => 'Le client rencontre un problème avec son imprimante réseau.',
                    'categorie_proposee' => 'Technique',
                    'priorite_proposee' => 'moyenne',
                    'brouillon_reponse' => 'Bonjour, pouvez-vous nous préciser le modèle de votre imprimante ?',
                ]);
        }
    );

    $job = new AnalyzeTicketJob($this->ticket);

    $job->handle(
        app(GhostwriterService::class)
    );

    $this->assertDatabaseHas('ai_suggestions', [
        'ticket_id' => $this->ticket->id,
        'resume' => 'Le client rencontre un problème avec son imprimante réseau.',
        'categorie_proposee' => 'Technique',
        'priorite_proposee' => 'moyenne',
        'statut' => 'en_attente_validation',
    ]);
});

test('la suggestion créée appartient au bon ticket', function () {
    $this->mock(
        GhostwriterService::class,
        function (MockInterface $mock) {
            $mock->shouldReceive('analyser')
                ->once()
                ->andReturn([
                    'resume' => 'Résumé.',
                    'categorie_proposee' => 'Technique',
                    'priorite_proposee' => 'basse',
                    'brouillon_reponse' => 'Brouillon.',
                ]);
        }
    );

    $job = new AnalyzeTicketJob($this->ticket);

    $job->handle(
        app(GhostwriterService::class)
    );

    $suggestion = AiSuggestion::firstOrFail();

    expect($suggestion->ticket->id)
        ->toBe($this->ticket->id);
});

test('le job enregistre le brouillon de réponse', function () {
    $this->mock(
        GhostwriterService::class,
        function (MockInterface $mock) {
            $mock->shouldReceive('analyser')
                ->once()
                ->andReturn([
                    'resume' => 'Résumé.',
                    'categorie_proposee' => 'Technique',
                    'priorite_proposee' => 'haute',
                    'brouillon_reponse' => 'Bonjour, nous allons analyser votre problème.',
                ]);
        }
    );

    $job = new AnalyzeTicketJob($this->ticket);

    $job->handle(
        app(GhostwriterService::class)
    );

    $this->assertDatabaseHas('ai_suggestions', [
        'ticket_id' => $this->ticket->id,
        'brouillon_reponse' => 'Bonjour, nous allons analyser votre problème.',
    ]);
});
