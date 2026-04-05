<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * ==========================================
 * Login Tests
 * ==========================================
 */
test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertSuccessful();
    $response->assertSee('Log in');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('chat.show'));
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('users cannot authenticate with invalid email', function () {
    $response = $this->post(route('login'), [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('remember me creates persistent cookie', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'password123',
        'remember' => true,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('chat.show'));

    // Verify the remember token was set on the user
    $user->refresh();
    expect($user->remember_token)->not->toBeNull();
});

test('email is required to login', function () {
    $response = $this->post(route('login'), [
        'email' => '',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('password is required to login', function () {
    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => '',
    ]);

    $response->assertSessionHasErrors('password');
});

/**
 * ==========================================
 * Registration Tests
 * ==========================================
 */
test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertSuccessful();
    $response->assertSee('Register');
});

test('new users can register', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('chat.show'));

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'newuser@example.com',
    ]);
});

test('name is required to register', function () {
    $response = $this->post(route('register'), [
        'name' => '',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('name');
});

test('email is required to register', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => '',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('email must be valid format', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('email must be unique', function () {
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('password is required to register', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => '',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('password');
});

test('password must be confirmed', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors('password');
});

/**
 * ==========================================
 * Logout Tests
 * ==========================================
 */
test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('users cannot logout when not authenticated', function () {
    $response = $this->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});

/**
 * ==========================================
 * Authentication Redirect Tests
 * ==========================================
 */
test('authenticated users are redirected to chat from login page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('login'));

    $response->assertRedirect(route('dashboard'));
});

test('authenticated users are redirected to chat from register page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('register'));

    $response->assertRedirect(route('dashboard'));
});
