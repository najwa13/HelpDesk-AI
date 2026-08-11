<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Article;
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

    $this->agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $this->ticket = Ticket::create([
        'titre' => 'Erreur 500 sur toutes les pages',
        'description' => 'L\'application Laravel ne démarre plus.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'agent_id' => $this->agent->id,
        'categorie_id' => $this->category->id,
    ]);
});

test('un invité est redirigé vers le login depuis admin/tickets', function () {
    $this->get('/admin/tickets')
        ->assertRedirect('/login');
});

test('un invité est redirigé vers le login depuis admin/management', function () {
    $this->get('/admin/management')
        ->assertRedirect('/login');
});

test('un invité est redirigé vers le login depuis admin/knowledge', function () {
    $this->get('/admin/knowledge')
        ->assertRedirect('/login');
});

test('un admin peut lister tous les tickets', function () {
    $this->actingAs($this->admin);

    $this->get('/admin/tickets')
        ->assertOk()
        ->assertSee('Erreur 500 sur toutes les pages');
});

test('un admin peut voir le détail d\'un ticket', function () {
    $this->actingAs($this->admin);

    $this->get("/admin/tickets/{$this->ticket->id}")
        ->assertOk()
        ->assertSee('Erreur 500 sur toutes les pages');
});

test('un admin peut assigner un ticket à un agent', function () {
    $newAgent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $this->actingAs($this->admin);

    $this->post("/admin/tickets/{$this->ticket->id}/assign", [
        'agent_id' => $newAgent->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'id' => $this->ticket->id,
        'agent_id' => $newAgent->id,
    ]);
});

test('un agent ne peut pas accéder à la page admin des tickets', function () {
    $this->actingAs($this->agent);

    $this->get('/admin/tickets')
        ->assertForbidden();
});

test('un client ne peut pas accéder à la page admin des tickets', function () {
    $this->actingAs($this->client);

    $this->get('/admin/tickets')
        ->assertForbidden();
});

test('un admin accède à la gestion des agents et catégories', function () {
    $this->actingAs($this->admin);

    $this->get('/admin/management')
        ->assertOk()
        ->assertSee($this->agent->name)
        ->assertSee('Technique');
});

test('un agent ne peut pas accéder à la gestion admin', function () {
    $this->actingAs($this->agent);

    $this->get('/admin/management')
        ->assertForbidden();
});

test('un admin peut créer une catégorie', function () {
    $this->actingAs($this->admin);

    $this->post('/admin/management/categories', [
        'nom' => 'Facturation',
        'description' => 'Problèmes de facturation',
    ])->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'nom' => 'Facturation',
    ]);
});

test('un admin ne peut pas supprimer une catégorie utilisée', function () {
    $this->actingAs($this->admin);

    $this->delete("/admin/management/categories/{$this->category->id}")
        ->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'id' => $this->category->id,
    ]);
});

test('un admin peut supprimer une catégorie inutilisée', function () {
    $empty = Category::create([
        'nom' => 'Vide',
    ]);

    $this->actingAs($this->admin);

    $this->delete("/admin/management/categories/{$empty->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('categories', [
        'id' => $empty->id,
    ]);
});

test('un admin accède à la base de connaissances', function () {
    Article::create([
        'titre' => 'Résoudre une erreur 500',
        'contenu' => 'Vider le cache de configuration.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    $this->actingAs($this->admin);

    $this->get('/admin/knowledge')
        ->assertOk()
        ->assertSee('Résoudre une erreur 500');
});

test('un agent ne peut pas accéder à la base de connaissances admin', function () {
    $this->actingAs($this->agent);

    $this->get('/admin/knowledge')
        ->assertForbidden();
});

test('un admin peut créer un article', function () {
    $this->actingAs($this->admin);

    $this->post('/admin/knowledge', [
        'titre' => 'Nouveau guide',
        'contenu' => 'Guide détaillé.',
        'categorie_id' => $this->category->id,
        'publier' => 1,
    ])->assertRedirect(route('admin.knowledge.index'));

    $this->assertDatabaseHas('articles', [
        'titre' => 'Nouveau guide',
    ]);
});

test('un admin peut modifier un article', function () {
    $article = Article::create([
        'titre' => 'Ancien titre',
        'contenu' => 'Ancien contenu.',
        'categorie_id' => $this->category->id,
    ]);

    $this->actingAs($this->admin);

    $this->put("/admin/knowledge/{$article->id}", [
        'titre' => 'Titre modifié',
        'contenu' => 'Contenu modifié.',
        'categorie_id' => $this->category->id,
        'publier' => 0,
    ])->assertRedirect(route('admin.knowledge.index'));

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'titre' => 'Titre modifié',
        'published_at' => null,
    ]);
});

test('un admin peut supprimer un article', function () {
    $article = Article::create([
        'titre' => 'À supprimer',
        'contenu' => 'Contenu.',
        'categorie_id' => $this->category->id,
    ]);

    $this->actingAs($this->admin);

    $this->delete("/admin/knowledge/{$article->id}")
        ->assertRedirect(route('admin.knowledge.index'));

    $this->assertDatabaseMissing('articles', [
        'id' => $article->id,
    ]);
});

test('la sidebar admin n\'affiche plus d\'item Assistant IA', function () {
    $this->actingAs($this->admin);

    $this->get('/admin/dashboard')
        ->assertOk()
        ->assertDontSee('Assistant IA')
        ->assertSee('Base de connaissances');
});
