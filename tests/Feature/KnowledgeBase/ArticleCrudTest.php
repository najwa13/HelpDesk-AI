<?php

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::create([
        'nom' => 'Technique',
        'description' => 'Articles techniques.',
    ]);

    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $this->agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $this->client = User::factory()->create([
        'role' => UserRole::Client,
    ]);
});

test('un administrateur peut créer un article', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/articles', [
        'titre' => 'Réinitialiser son mot de passe',
        'contenu' => 'Cliquez sur mot de passe oublié.',
        'categorie_id' => $this->category->id,
        'published_at' => now()->toDateTimeString(),
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.titre',
            'Réinitialiser son mot de passe'
        )
        ->assertJsonPath(
            'data.categorie.id',
            $this->category->id
        );

    $this->assertDatabaseHas('articles', [
        'titre' => 'Réinitialiser son mot de passe',
        'categorie_id' => $this->category->id,
    ]);
});

test('un administrateur peut consulter la liste des articles', function () {
    Article::create([
        'titre' => 'Article test',
        'contenu' => 'Contenu test.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    Sanctum::actingAs($this->admin);

    $response = $this->getJson('/api/v1/articles');

    $response
        ->assertOk()
        ->assertJsonFragment([
            'titre' => 'Article test',
        ]);
});

test('un administrateur peut consulter un article', function () {
    $article = Article::create([
        'titre' => 'Article individuel',
        'contenu' => 'Contenu.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    Sanctum::actingAs($this->admin);

    $response = $this->getJson(
        "/api/v1/articles/{$article->id}"
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.id',
            $article->id
        )
        ->assertJsonPath(
            'data.titre',
            'Article individuel'
        );
});

test('un administrateur peut modifier un article', function () {
    $article = Article::create([
        'titre' => 'Ancien titre',
        'contenu' => 'Ancien contenu.',
        'categorie_id' => $this->category->id,
        'published_at' => null,
    ]);

    Sanctum::actingAs($this->admin);

    $response = $this->patchJson(
        "/api/v1/articles/{$article->id}",
        [
            'titre' => 'Nouveau titre',
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.titre',
            'Nouveau titre'
        );

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'titre' => 'Nouveau titre',
    ]);
});

test('un administrateur peut supprimer un article', function () {
    $article = Article::create([
        'titre' => 'Article à supprimer',
        'contenu' => 'Contenu.',
        'categorie_id' => $this->category->id,
    ]);

    Sanctum::actingAs($this->admin);

    $this->deleteJson(
        "/api/v1/articles/{$article->id}"
    )->assertNoContent();

    $this->assertDatabaseMissing('articles', [
        'id' => $article->id,
    ]);
});

test('un agent ne peut pas gérer les articles', function () {
    Sanctum::actingAs($this->agent);

    $this->postJson('/api/v1/articles', [
        'titre' => 'Article interdit',
        'contenu' => 'Contenu.',
        'categorie_id' => $this->category->id,
    ])->assertForbidden();

    $this->assertDatabaseMissing('articles', [
        'titre' => 'Article interdit',
    ]);
});

test('un client ne peut pas gérer les articles', function () {
    Sanctum::actingAs($this->client);

    $this->postJson('/api/v1/articles', [
        'titre' => 'Article interdit client',
        'contenu' => 'Contenu.',
        'categorie_id' => $this->category->id,
    ])->assertForbidden();

    $this->assertDatabaseMissing('articles', [
        'titre' => 'Article interdit client',
    ]);
});

test('titre contenu et categorie sont obligatoires', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/articles', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'titre',
            'contenu',
            'categorie_id',
        ]);
});

test('la catégorie doit exister', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/articles', [
        'titre' => 'Article test',
        'contenu' => 'Contenu test.',
        'categorie_id' => 999999,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('categorie_id');
});

test('un article peut rester en brouillon', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/articles', [
        'titre' => 'Article brouillon',
        'contenu' => 'Contenu en cours de rédaction.',
        'categorie_id' => $this->category->id,
        'published_at' => null,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.published_at', null);

    $this->assertDatabaseHas('articles', [
        'titre' => 'Article brouillon',
        'published_at' => null,
    ]);
});
