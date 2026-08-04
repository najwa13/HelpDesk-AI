<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('un administrateur peut créer une catégorie', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/categories', [
        'nom' => 'Facturation',
        'description' => 'Problèmes liés à la facturation.',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.nom', 'Facturation');

    $this->assertDatabaseHas('categories', [
        'nom' => 'Facturation',
        'description' => 'Problèmes liés à la facturation.',
    ]);
});

test('un administrateur peut modifier une catégorie', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $category = Category::create([
        'nom' => 'Facturation',
        'description' => 'Ancienne description.',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->patchJson(
        "/api/v1/categories/{$category->id}",
        [
            'nom' => 'Facturation et paiements',
            'description' => 'Questions liées aux paiements.',
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.nom',
            'Facturation et paiements'
        );

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'nom' => 'Facturation et paiements',
    ]);
});

test('un administrateur peut supprimer une catégorie', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $category = Category::create([
        'nom' => 'Catégorie temporaire',
        'description' => 'À supprimer.',
    ]);

    Sanctum::actingAs($admin);

    $this->deleteJson(
        "/api/v1/categories/{$category->id}"
    )->assertNoContent();

    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});

test('un agent ne peut pas créer une catégorie', function () {
    $agent = User::factory()->create([
        'role' => UserRole::Agent,
    ]);

    Sanctum::actingAs($agent);

    $this->postJson('/api/v1/categories', [
        'nom' => 'Interdit',
        'description' => 'Cette catégorie ne doit pas être créée.',
    ])->assertForbidden();

    $this->assertDatabaseMissing('categories', [
        'nom' => 'Interdit',
    ]);
});

test('un client ne peut pas créer une catégorie', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    Sanctum::actingAs($client);

    $this->postJson('/api/v1/categories', [
        'nom' => 'Interdit client',
        'description' => 'Cette catégorie ne doit pas être créée.',
    ])->assertForbidden();

    $this->assertDatabaseMissing('categories', [
        'nom' => 'Interdit client',
    ]);
});

test('le nom de la catégorie est obligatoire', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    Sanctum::actingAs($admin);

    $this->postJson('/api/v1/categories', [
        'description' => 'Catégorie sans nom.',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('nom');
});
