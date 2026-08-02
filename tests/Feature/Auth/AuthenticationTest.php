<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('un client peut créer un compte', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Client Test',
        'email' => 'client@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Compte créé avec succès.')
        ->assertJsonPath('data.user.name', 'Client Test')
        ->assertJsonPath('data.user.email', 'client@example.com')
        ->assertJsonPath('data.user.role', UserRole::Client->value)
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonStructure([
            'message',
            'data' => [
                'user',
                'token',
                'token_type',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Client Test',
        'email' => 'client@example.com',
        'role' => UserRole::Client->value,
    ]);
});

test('le nom est obligatoire', function () {
    $response = $this->postJson('/api/v1/register', [
        'email' => 'client@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('email doit être unique', function () {
    User::factory()->create([
        'email' => 'client@example.com',
    ]);

    $response = $this->postJson('/api/v1/register', [
        'name' => 'Client Test',
        'email' => 'client@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('le mot de passe doit être confirmé', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Client Test',
        'email' => 'client@example.com',
        'password' => 'password123',
        'password_confirmation' => 'mot-de-passe-different',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

test('le rôle envoyé par le client est ignoré', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Client Test',
        'email' => 'client@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.user.role', UserRole::Client->value);

    $this->assertDatabaseHas('users', [
        'email' => 'client@example.com',
        'role' => UserRole::Client->value,
    ]);
});

test('un utilisateur peut se connecter', function () {
    $user = User::factory()->create([
        'email' => 'client@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'client@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Connexion réussie.')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', 'client@example.com')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonStructure([
            'message',
            'data' => [
                'user',
                'token',
                'token_type',
            ],
        ]);

    expect($response->json('data.token'))->not->toBeEmpty();
});
test('un utilisateur ne peut pas se connecter avec un mauvais mot de passe', function () {
    User::factory()->create([
        'email' => 'client@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'client@example.com',
        'password' => 'mauvais-password',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('un utilisateur ne peut pas se connecter avec un email inconnu', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => 'inconnu@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('email et mot de passe sont obligatoires pour se connecter', function () {
    $response = $this->postJson('/api/v1/login', []);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'email',
            'password',
        ]);
});
test('un utilisateur authentifié peut récupérer son profil', function () {
    $user = User::factory()->create();

    $token = $user->createToken('api-token')->plainTextToken;

    $response = $this
        ->withToken($token)
        ->getJson('/api/v1/user');

    $response
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('email', $user->email)
        ->assertJsonPath('role', UserRole::Client->value);
});
test('un utilisateur non authentifié ne peut pas récupérer son profil', function () {
    $response = $this->getJson('/api/v1/user');

    $response
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});
test('un utilisateur authentifié peut se déconnecter', function () {
    $user = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $token = $user->createToken('api-token')->plainTextToken;

    $response = $this
        ->withToken($token)
        ->postJson('/api/v1/logout');

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Déconnexion réussie.');

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('un utilisateur non authentifié ne peut pas se déconnecter', function () {
    $this->postJson('/api/v1/logout')
        ->assertUnauthorized();
});
