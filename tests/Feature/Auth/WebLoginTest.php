<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('la page de login est accessible par un invité', function () {
    $response = $this->get('/login');

    $response
        ->assertOk()
        ->assertSee('Connexion')
        ->assertSee('Adresse e-mail');
});

test('un admin peut se connecter et est redirigé vers le dashboard', function () {
    $user = User::factory()->create([
        'email' => 'admin@helpdesk.ai',
        'password' => Hash::make('password123'),
        'role' => UserRole::Admin,
    ]);

    $response = $this->post('/login', [
        'email' => 'admin@helpdesk.ai',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/admin/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('un agent peut se connecter et est redirigé vers la page d\'accueil', function () {
    $user = User::factory()->create([
        'email' => 'agent@helpdesk.ai',
        'password' => Hash::make('password123'),
        'role' => UserRole::Agent,
    ]);

    $response = $this->post('/login', [
        'email' => 'agent@helpdesk.ai',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
});

test('un client peut se connecter et est redirigé vers la page d\'accueil', function () {
    $user = User::factory()->create([
        'email' => 'client@helpdesk.ai',
        'password' => Hash::make('password123'),
        'role' => UserRole::Client,
    ]);

    $response = $this->post('/login', [
        'email' => 'client@helpdesk.ai',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
});

test('des identifiants incorrects rejettent la connexion', function () {
    User::factory()->create([
        'email' => 'user@helpdesk.ai',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => 'user@helpdesk.ai',
        'password' => 'mauvais-password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('la déconnexion fonctionne correctement', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $this->actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});

test('un utilisateur déjà connecté est redirigé depuis /login', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $this->actingAs($user);

    $response = $this->get('/login');

    $response->assertRedirect();
});
