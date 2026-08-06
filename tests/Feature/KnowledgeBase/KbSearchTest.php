<?php

use App\Enums\UserRole;
use App\Jobs\DetectSearchLanguageJob;
use App\Models\Article;
use App\Models\Category;
use App\Models\SearchLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use Laravel\Sanctum\Sanctum;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->category = Category::create([
        'nom' => 'Technique',
        'description' => 'Problèmes techniques',
    ]);

    $this->client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $this->otherClient = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    /*
     * On ajoute plusieurs articles publiés pour avoir un vrai petit corpus
     * FULLTEXT. Avec un seul article, MySQL peut retourner un score nul.
     */
    Article::create([
        'titre' => 'Configurer les notifications',
        'contenu' => 'Procédure permettant de gérer les notifications du compte.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    Article::create([
        'titre' => 'Modifier son profil',
        'contenu' => 'Procédure permettant de modifier les informations du profil.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    Article::create([
        'titre' => 'Configurer une imprimante',
        'contenu' => 'Guide de configuration des imprimantes réseau.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    Article::create([
        'titre' => 'Problème de facturation',
        'contenu' => 'Informations concernant les factures et les paiements.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    $this->article = Article::create([
        'titre' => 'Réinitialiser son mot de passe',
        'contenu' => 'Cliquez sur mot de passe oublié pour réinitialiser votre mot de passe.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);
});

test('un client peut rechercher un article publié', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $response = $this->postJson('/api/v1/kb/search', [
        'requete' => 'mot de passe oublié',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('trouve', true)
        ->assertJsonPath('article.id', $this->article->id)
        ->assertJsonPath(
            'article.titre',
            'Réinitialiser son mot de passe'
        )
        ->assertJsonStructure([
            'score_correspondance',
            'search_log_id',
        ]);
});

test('une recherche est journalisée', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $this->postJson('/api/v1/kb/search', [
        'requete' => 'mot de passe oublié',
    ])->assertOk();

    $this->assertDatabaseHas('search_logs', [
        'requete_originale' => 'mot de passe oublié',
        'requete_normalisee' => 'mot de passe oublié',
        'client_id' => $this->client->id,
        'article_id' => $this->article->id,
        'resultat' => 'trouve',
    ]);
});

test('la détection de langue est envoyée dans la queue', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $this->postJson('/api/v1/kb/search', [
        'requete' => 'mot de passe oublié',
    ])->assertOk();

    Bus::assertDispatched(
        DetectSearchLanguageJob::class,
        function (DetectSearchLanguageJob $job) {
            return $job->searchLog->client_id === $this->client->id;
        }
    );
});

test('un article brouillon n apparait pas dans les résultats', function () {
    Bus::fake();

    $brouillon = Article::create([
        'titre' => 'Configuration vpn entreprise secretxyz',
        'contenu' => 'Procédure spéciale secretxyz pour configurer le vpn entreprise.',
        'categorie_id' => $this->category->id,
        'published_at' => null,
    ]);

    Sanctum::actingAs($this->client);

    $response = $this->postJson('/api/v1/kb/search', [
        'requete' => 'vpn entreprise secretxyz',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('trouve', false);

    $this->assertDatabaseHas('articles', [
        'id' => $brouillon->id,
        'published_at' => null,
    ]);
});

test('une recherche sans correspondance est journalisée', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $response = $this->postJson('/api/v1/kb/search', [
        'requete' => 'question totalement inconnue sans article xyzabc',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('trouve', false)
        ->assertJsonStructure([
            'search_log_id',
            'message',
        ]);

    $this->assertDatabaseHas('search_logs', [
        'requete_originale' => 'question totalement inconnue sans article xyzabc',
        'resultat' => 'non_trouve',
        'client_id' => $this->client->id,
        'article_id' => null,
        'ticket_id' => null,
    ]);
});

test('un client peut créer un ticket depuis une recherche sans résultat', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $searchResponse = $this->postJson('/api/v1/kb/search', [
        'requete' => 'zxqvplmn',
    ]);

    $searchResponse
        ->assertOk()
        ->assertJsonPath('trouve', false);

    $searchLogId = $searchResponse->json('search_log_id');

    $response = $this->postJson(
        "/api/v1/kb/search/{$searchLogId}/ticket",
        [
            'titre' => 'Problème périphérique inconnu',
            'description' => 'Mon problème ne correspond à aucun article de la base de connaissances.',
            'categorie_id' => $this->category->id,
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.titre',
            'Problème périphérique inconnu'
        )
        ->assertJsonPath(
            'data.statut',
            'ouvert'
        );

    $searchLog = SearchLog::findOrFail($searchLogId);

    expect($searchLog->ticket_id)->not->toBeNull()
        ->and($searchLog->resultat)->toBe('ticket_cree');

    $this->assertDatabaseHas('tickets', [
        'id' => $searchLog->ticket_id,
        'client_id' => $this->client->id,
        'titre' => 'Problème périphérique inconnu',
    ]);
});

test('un deuxième ticket ne peut pas être créé depuis la même recherche', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $searchLog = SearchLog::create([
        'requete_originale' => 'problème inconnu',
        'requete_normalisee' => 'problème inconnu',
        'langue_detectee' => 'fr',
        'resultat' => 'non_trouve',
        'score_correspondance' => null,
        'client_id' => $this->client->id,
        'article_id' => null,
        'ticket_id' => null,
    ]);

    $body = [
        'titre' => 'Problème inconnu',
        'description' => 'Description suffisamment détaillée du problème.',
        'categorie_id' => $this->category->id,
    ];

    $this->postJson(
        "/api/v1/kb/search/{$searchLog->id}/ticket",
        $body
    )->assertCreated();

    $this->postJson(
        "/api/v1/kb/search/{$searchLog->id}/ticket",
        $body
    )
        ->assertUnprocessable()
        ->assertJsonPath(
            'message',
            'Un ticket a déjà été créé depuis cette recherche.'
        );
});

test('un client ne peut pas utiliser la recherche d un autre client', function () {
    $searchLog = SearchLog::create([
        'requete_originale' => 'problème inconnu',
        'requete_normalisee' => 'problème inconnu',
        'langue_detectee' => 'fr',
        'resultat' => 'non_trouve',
        'score_correspondance' => null,
        'client_id' => $this->client->id,
        'article_id' => null,
        'ticket_id' => null,
    ]);

    Sanctum::actingAs($this->otherClient);

    $this->postJson(
        "/api/v1/kb/search/{$searchLog->id}/ticket",
        [
            'titre' => 'Tentative interdite',
            'description' => 'Tentative depuis un autre compte client.',
            'categorie_id' => $this->category->id,
        ]
    )->assertNotFound();
});

test('une requête de recherche est obligatoire', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $this->postJson('/api/v1/kb/search', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('requete');
});

test('une requête de recherche doit contenir au moins trois caractères', function () {
    Bus::fake();

    Sanctum::actingAs($this->client);

    $this->postJson('/api/v1/kb/search', [
        'requete' => 'ab',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('requete');
});
