<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
