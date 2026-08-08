<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('la page d\'inscription est accessible par un invité', function () {
    $response = $this->get('/register');

    $response
        ->assertOk()
        ->assertSee('Inscription')
        ->assertSee('Nom complet');
});

test('un client peut créer un compte valide', function () {
    $response = $this->post('/register', [
        'name' => 'Jean Dupont',
        'email' => 'jean@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/');

    $this->assertDatabaseHas('users', [
        'name' => 'Jean Dupont',
        'email' => 'jean@example.com',
    ]);
});

test('le compte est enregistré en base avec role = client', function () {
    $this->post('/register', [
        'name' => 'Jean Dupont',
        'email' => 'jean@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'jean@example.com',
        'role' => UserRole::Client->value,
    ]);
});

test('le nouvel utilisateur est authentifié après inscription', function () {
    $this->post('/register', [
        'name' => 'Jean Dupont',
        'email' => 'jean@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
});

test('email doit être unique', function () {
    User::factory()->create([
        'email' => 'jean@example.com',
    ]);

    $response = $this->post('/register', [
        'name' => 'Jean Dupont',
        'email' => 'jean@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertRedirect()
        ->assertSessionHasErrors('email');
});

test('password doit être confirmé', function () {
    $response = $this->post('/register', [
        'name' => 'Jean Dupont',
        'email' => 'jean@example.com',
        'password' => 'password123',
        'password_confirmation' => 'mot-de-passe-different',
    ]);

    $response
        ->assertRedirect()
        ->assertSessionHasErrors('password');
});

test('le rôle envoyé manuellement admin est ignoré et devient client', function () {
    $this->post('/register', [
        'name' => 'Hacker',
        'email' => 'hacker@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'hacker@example.com',
        'role' => UserRole::Client->value,
    ]);

    $this->assertDatabaseMissing('users', [
        'email' => 'hacker@example.com',
        'role' => UserRole::Admin->value,
    ]);
});

test('le rôle envoyé manuellement agent est ignoré et devient client', function () {
    $this->post('/register', [
        'name' => 'Hacker',
        'email' => 'hacker@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'agent',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'hacker@example.com',
        'role' => UserRole::Client->value,
    ]);

    $this->assertDatabaseMissing('users', [
        'email' => 'hacker@example.com',
        'role' => UserRole::Agent->value,
    ]);
});

test('un utilisateur déjà authentifié est redirigé depuis /register', function () {
    $user = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $this->actingAs($user);

    $response = $this->get('/register');

    $response->assertRedirect();
});
