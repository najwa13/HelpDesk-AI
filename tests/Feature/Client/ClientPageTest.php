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

    $this->otherClient = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $this->agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $this->ticket = Ticket::create([
        'titre' => 'Impossible de me connecter',
        'description' => 'J\'ai une erreur de connexion depuis hier.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->client->id,
        'categorie_id' => $this->category->id,
    ]);
});

test('un invité est redirigé vers le login depuis client/tickets', function () {
    $this->get('/client/tickets')
        ->assertRedirect('/login');
});

test('un client peut accéder à la liste de ses tickets', function () {
    $this->actingAs($this->client);

    $this->get('/client/tickets')
        ->assertOk()
        ->assertSee('Mes tickets')
        ->assertSee('Impossible de me connecter');
});

test('un client ne voit que ses propres tickets', function () {
    $otherTicket = Ticket::create([
        'titre' => 'Ticket d\'un autre client',
        'description' => 'Ceci est confidentiel.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->otherClient->id,
        'categorie_id' => $this->category->id,
    ]);

    $this->actingAs($this->client);

    $response = $this->get('/client/tickets');
    $response->assertOk();
    $response->assertDontSee('Ticket d\'un autre client');
});

test('un agent ne peut pas accéder à la liste des tickets client', function () {
    $this->actingAs($this->agent);

    $this->get('/client/tickets')
        ->assertForbidden();
});

test('un admin ne peut pas accéder à la liste des tickets client', function () {
    $this->actingAs($this->admin);

    $this->get('/client/tickets')
        ->assertForbidden();
});

test('un client peut accéder au détail de son ticket', function () {
    $this->actingAs($this->client);

    $this->get("/client/tickets/{$this->ticket->id}")
        ->assertOk()
        ->assertSee('Impossible de me connecter');
});

test('un client ne peut pas voir le ticket d\'un autre client', function () {
    $otherTicket = Ticket::create([
        'titre' => 'Ticket confidentiel',
        'description' => 'Pas pour moi.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->otherClient->id,
        'categorie_id' => $this->category->id,
    ]);

    $this->actingAs($this->client);

    $this->get("/client/tickets/{$otherTicket->id}")
        ->assertForbidden();
});

test('un agent ne peut pas voir le détail d\'un ticket client', function () {
    $this->actingAs($this->agent);

    $this->get("/client/tickets/{$this->ticket->id}")
        ->assertForbidden();
});

test('un client peut créer une demande', function () {
    $this->actingAs($this->client);

    $this->get('/client/tickets/create')
        ->assertOk()
        ->assertSee('Nouvelle demande');

    $response = $this->post('/client/tickets', [
        'titre' => 'Facture en double',
        'description' => 'J\'ai reçu deux factures pour le même mois, pouvez-vous corriger ?',
        'categorie_id' => $this->category->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tickets', [
        'titre' => 'Facture en double',
        'client_id' => $this->client->id,
        'statut' => 'ouvert',
    ]);
});

test('la création d une demande exige les champs requis', function () {
    $this->actingAs($this->client);

    $response = $this->post('/client/tickets', []);

    $response->assertSessionHasErrors(['titre', 'description', 'categorie_id']);
});

test('un agent ne peut pas créer une demande côté client', function () {
    $this->actingAs($this->agent);

    $this->get('/client/tickets/create')
        ->assertForbidden();

    $this->post('/client/tickets', [
        'titre' => 'Tentative',
        'description' => 'Tentative de création par un agent.',
        'categorie_id' => $this->category->id,
    ])->assertForbidden();
});

test('un client peut répondre sur son ticket', function () {
    $this->actingAs($this->client);

    $this->post("/client/tickets/{$this->ticket->id}/messages", [
        'contenu' => 'Voici plus de précisions sur mon problème.',
    ])->assertRedirect();

    $this->assertDatabaseHas('messages', [
        'ticket_id' => $this->ticket->id,
        'auteur_id' => $this->client->id,
        'contenu' => 'Voici plus de précisions sur mon problème.',
    ]);
});

test('un client ne peut pas répondre sur le ticket d\'un autre client', function () {
    $otherTicket = Ticket::create([
        'titre' => 'Ticket confidentiel',
        'description' => 'Pas pour moi.',
        'statut' => TicketStatus::Ouvert,
        'client_id' => $this->otherClient->id,
        'categorie_id' => $this->category->id,
    ]);

    $this->actingAs($this->client);

    $this->post("/client/tickets/{$otherTicket->id}/messages", [
        'contenu' => 'Intrusion',
    ])->assertForbidden();
});

test('un client peut accéder à l assistant IA', function () {
    $this->actingAs($this->client);

    $this->get('/client/assistant')
        ->assertOk()
        ->assertSee('Assistant client');
});

test('un agent ne peut pas accéder à l assistant client', function () {
    $this->actingAs($this->agent);

    $this->get('/client/assistant')
        ->assertForbidden();
});

test('un client peut accéder au centre d aide', function () {
    Article::create([
        'titre' => 'Réinitialiser mon mot de passe',
        'contenu' => 'Cliquez sur Mot de passe oublié.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    $this->actingAs($this->client);

    $this->get('/client/help')
        ->assertOk()
        ->assertSee('Centre d\'aide')
        ->assertSee('Réinitialiser mon mot de passe');
});

test('un client ne voit que les articles publiés dans le centre d aide', function () {
    Article::create([
        'titre' => 'Brouillon interne',
        'contenu' => 'Ne doit pas apparaître.',
        'categorie_id' => $this->category->id,
        'published_at' => null,
    ]);

    $this->actingAs($this->client);

    $this->get('/client/help')
        ->assertOk()
        ->assertDontSee('Brouillon interne');
});

test('un client peut ouvrir un article publié du centre d aide', function () {
    $article = Article::create([
        'titre' => 'Réinitialiser mon mot de passe',
        'contenu' => 'Cliquez sur Mot de passe oublié.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    $this->actingAs($this->client);

    $this->get("/client/help/{$article->id}")
        ->assertOk()
        ->assertSee('Réinitialiser mon mot de passe');
});

test('un client ne peut pas ouvrir un article non publié', function () {
    $article = Article::create([
        'titre' => 'Brouillon interne',
        'contenu' => 'Ne doit pas apparaître.',
        'categorie_id' => $this->category->id,
        'published_at' => null,
    ]);

    $this->actingAs($this->client);

    $this->get("/client/help/{$article->id}")
        ->assertNotFound();
});

test('un agent ne peut pas accéder au centre d aide client', function () {
    $this->actingAs($this->agent);

    $this->get('/client/help')
        ->assertForbidden();
});
