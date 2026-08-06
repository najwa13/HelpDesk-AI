<?php

use App\Ai\Agents\TicketAnalyzer;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Services\GhostwriterService;
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

    $this->ticket = Ticket::create([
        'titre' => 'Problème imprimante réseau',
        'description' => 'Mon imprimante réseau ne fonctionne plus.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'categorie_id' => $this->category->id,
    ]);
});

test('ghostwriter retourne une analyse structurée valide', function () {
    TicketAnalyzer::fake([
        [
            'resume' => 'Le client rencontre un problème avec son imprimante réseau.',
            'categorie_proposee' => 'Technique',
            'priorite_proposee' => 'moyenne',
            'brouillon_reponse' => 'Bonjour, pouvez-vous nous préciser le modèle de votre imprimante ?',
        ],
    ])->preventStrayPrompts();

    $resultat = app(GhostwriterService::class)
        ->analyser($this->ticket);

    expect($resultat)
        ->toHaveKeys([
            'resume',
            'categorie_proposee',
            'priorite_proposee',
            'brouillon_reponse',
        ])
        ->and($resultat['resume'])
        ->toBe('Le client rencontre un problème avec son imprimante réseau.')
        ->and($resultat['categorie_proposee'])
        ->toBe('Technique')
        ->and($resultat['priorite_proposee'])
        ->toBe('moyenne')
        ->and($resultat['brouillon_reponse'])
        ->toBe('Bonjour, pouvez-vous nous préciser le modèle de votre imprimante ?');
});

test('ghostwriter refuse une priorité inexistante', function () {
    TicketAnalyzer::fake([
        [
            'resume' => 'Résumé du ticket.',
            'categorie_proposee' => 'Technique',
            'priorite_proposee' => 'urgente',
            'brouillon_reponse' => 'Brouillon.',
        ],
    ])->preventStrayPrompts();

    expect(
        fn () => app(GhostwriterService::class)
            ->analyser($this->ticket)
    )->toThrow(
        UnexpectedValueException::class,
        'La priorité proposée par l’IA est invalide.'
    );
});

test('ghostwriter refuse une catégorie inexistante', function () {
    TicketAnalyzer::fake([
        [
            'resume' => 'Résumé du ticket.',
            'categorie_proposee' => 'Catégorie inventée',
            'priorite_proposee' => 'basse',
            'brouillon_reponse' => 'Brouillon.',
        ],
    ])->preventStrayPrompts();

    expect(
        fn () => app(GhostwriterService::class)
            ->analyser($this->ticket)
    )->toThrow(
        UnexpectedValueException::class,
        'La catégorie proposée par l’IA est invalide.'
    );
});

test('ghostwriter accepte la priorité basse', function () {
    TicketAnalyzer::fake([
        [
            'resume' => 'Résumé du ticket.',
            'categorie_proposee' => 'Technique',
            'priorite_proposee' => 'basse',
            'brouillon_reponse' => 'Brouillon.',
        ],
    ])->preventStrayPrompts();

    $resultat = app(GhostwriterService::class)
        ->analyser($this->ticket);

    expect($resultat['priorite_proposee'])
        ->toBe('basse');
});

test('ghostwriter accepte la priorité moyenne', function () {
    TicketAnalyzer::fake([
        [
            'resume' => 'Résumé du ticket.',
            'categorie_proposee' => 'Technique',
            'priorite_proposee' => 'moyenne',
            'brouillon_reponse' => 'Brouillon.',
        ],
    ])->preventStrayPrompts();

    $resultat = app(GhostwriterService::class)
        ->analyser($this->ticket);

    expect($resultat['priorite_proposee'])
        ->toBe('moyenne');
});

test('ghostwriter accepte la priorité haute', function () {
    TicketAnalyzer::fake([
        [
            'resume' => 'Résumé du ticket.',
            'categorie_proposee' => 'Technique',
            'priorite_proposee' => 'haute',
            'brouillon_reponse' => 'Brouillon.',
        ],
    ])->preventStrayPrompts();

    $resultat = app(GhostwriterService::class)
        ->analyser($this->ticket);

    expect($resultat['priorite_proposee'])
        ->toBe('haute');
});

test('ghostwriter utilise les catégories réellement présentes en base', function () {
    Category::create([
        'nom' => 'Facturation',
        'description' => 'Problèmes de facturation',
    ]);

    TicketAnalyzer::fake([
        [
            'resume' => 'Le client rencontre un problème de facturation.',
            'categorie_proposee' => 'Facturation',
            'priorite_proposee' => 'moyenne',
            'brouillon_reponse' => 'Nous allons vérifier votre facturation.',
        ],
    ])->preventStrayPrompts();

    $resultat = app(GhostwriterService::class)
        ->analyser($this->ticket);

    expect($resultat['categorie_proposee'])
        ->toBe('Facturation');
});
