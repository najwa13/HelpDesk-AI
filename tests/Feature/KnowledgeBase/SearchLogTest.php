<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Category;
use App\Models\SearchLog;
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
});

test('une recherche appartient à un client', function () {
    $searchLog = SearchLog::create([
        'requete_originale' => 'Je ne peux pas me connecter',
        'requete_normalisee' => 'je ne peux pas me connecter',
        'langue_detectee' => 'fr',
        'resultat' => 'aucun_resultat',
        'score_correspondance' => null,
        'client_id' => $this->client->id,
    ]);

    expect($searchLog->client->id)
        ->toBe($this->client->id);
});

test('une recherche peut être liée à un article trouvé', function () {
    $article = Article::create([
        'titre' => 'Problème de connexion',
        'contenu' => 'Voici la procédure à suivre.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    $searchLog = SearchLog::create([
        'requete_originale' => 'Problème connexion',
        'requete_normalisee' => 'probleme connexion',
        'langue_detectee' => 'fr',
        'resultat' => 'article_trouve',
        'score_correspondance' => 12.3456,
        'client_id' => $this->client->id,
        'article_id' => $article->id,
    ]);

    expect($searchLog->article->id)
        ->toBe($article->id);
});

test('une recherche peut aboutir à un ticket', function () {
    $ticket = Ticket::create([
        'titre' => 'Problème non résolu',
        'description' => 'Aucun article ne répond à mon problème.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'categorie_id' => $this->category->id,
    ]);

    $searchLog = SearchLog::create([
        'requete_originale' => 'Problème inconnu',
        'requete_normalisee' => 'probleme inconnu',
        'langue_detectee' => 'fr',
        'resultat' => 'ticket_cree',
        'score_correspondance' => null,
        'client_id' => $this->client->id,
        'ticket_id' => $ticket->id,
    ]);

    expect($searchLog->ticket->id)
        ->toBe($ticket->id);
});

test('une recherche peut ne trouver ni article ni ticket', function () {
    $searchLog = SearchLog::create([
        'requete_originale' => 'Question inconnue',
        'requete_normalisee' => 'question inconnue',
        'langue_detectee' => 'fr',
        'resultat' => 'aucun_resultat',
        'score_correspondance' => null,
        'client_id' => $this->client->id,
        'article_id' => null,
        'ticket_id' => null,
    ]);

    expect($searchLog->article)->toBeNull()
        ->and($searchLog->ticket)->toBeNull();
});

test('le score de correspondance est converti avec quatre décimales', function () {
    $searchLog = SearchLog::create([
        'requete_originale' => 'Mot de passe oublié',
        'requete_normalisee' => 'mot de passe oublie',
        'langue_detectee' => 'fr',
        'resultat' => 'article_trouve',
        'score_correspondance' => 15.4321,
        'client_id' => $this->client->id,
    ]);

    expect($searchLog->score_correspondance)
        ->toBe('15.4321');
});
