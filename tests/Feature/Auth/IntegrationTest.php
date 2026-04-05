<?php

use App\Ai\Agents\DevBot;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * ==========================================
 * Integration Tests: Full Auth + Chat Flow
 * ==========================================
 */

/**
 * Task 8.1: Create test user via registration
 * Task 8.2: Test login flow with redirect to /chat
 */
test('user can register and login with redirect to chat', function () {
    // Register new user
    $response = $this->post(route('register'), [
        'name' => 'Integration Test User',
        'email' => 'integration@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('chat.show'));

    // Logout
    $this->post(route('logout'));
    $this->assertGuest();

    // Login again
    $response = $this->post(route('login'), [
        'email' => 'integration@example.com',
        'password' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('chat.show'));
});

/**
 * Task 8.3: Test creating new conversation as authenticated user
 */
test('authenticated user can create conversation', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('chat.conversations.create'));

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('conversations', [
        'user_id' => $user->id,
        'title' => 'New Chat',
    ]);

    $conversation = Conversation::where('user_id', $user->id)->first();
    expect($conversation)->not->toBeNull();
    expect($conversation->user_id)->toBe($user->id);
});

/**
 * Task 8.4: Test conversation list shows only user's conversations
 */
test('conversation list shows only authenticated users conversations', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Create conversations for user1
    Conversation::factory()->count(3)->create(['user_id' => $user1->id]);

    // Create conversations for user2
    Conversation::factory()->count(2)->create(['user_id' => $user2->id]);

    // Check user1's conversation list
    $response = $this->actingAs($user1)->get(route('chat.show'));

    $response->assertSuccessful();

    // User1 should only see their 3 conversations
    $user1Conversations = Conversation::where('user_id', $user1->id)->get();
    expect($user1Conversations)->toHaveCount(3);

    foreach ($user1Conversations as $conversation) {
        $response->assertSee($conversation->title);
    }

    // User2's conversations should not be visible
    $user2Conversations = Conversation::where('user_id', $user2->id)->get();
    foreach ($user2Conversations as $conversation) {
        $response->assertDontSee($conversation->title);
    }
});

/**
 * Task 8.5: Test switching between conversations
 */
test('user can switch between their conversations', function () {
    $user = User::factory()->create();

    $conversation1 = Conversation::factory()->create([
        'user_id' => $user->id,
        'title' => 'First Conversation',
    ]);

    $conversation2 = Conversation::factory()->create([
        'user_id' => $user->id,
        'title' => 'Second Conversation',
    ]);

    // Access first conversation
    $response1 = $this->actingAs($user)->get(route('chat.show', ['conversation' => $conversation1]));
    $response1->assertSuccessful();
    $response1->assertSee('First Conversation');

    // Access second conversation
    $response2 = $this->actingAs($user)->get(route('chat.show', ['conversation' => $conversation2]));
    $response2->assertSuccessful();
    $response2->assertSee('Second Conversation');
});

test('user cannot access another users conversation', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $conversation = Conversation::factory()->create([
        'user_id' => $user1->id,
        'title' => 'User 1 Conversation',
    ]);

    // user2 tries to access user1's conversation - returns 404 (not 403 for security)
    $response = $this->actingAs($user2)->getJson(route('chat.conversations.get', $conversation));

    $response->assertNotFound();
});

/**
 * Task 8.6: Test sending messages to conversation
 */
test('user can send messages to their conversation', function () {
    $user = User::factory()->create();

    $conversation = Conversation::factory()->create([
        'user_id' => $user->id,
    ]);

    DevBot::fake(['AI response']);

    $response = $this->actingAs($user)->postJson(route('chat.messages.send'), [
        'message' => 'Hello, this is a test message!',
        'conversation_id' => $conversation->id,
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'role' => 'user',
    ]);

    $message = Message::where('conversation_id', $conversation->id)
        ->where('role', 'user')
        ->first();
    expect($message)->not->toBeNull();
    expect($message->content)->toContain('Hello, this is a test message!');
});

test('user cannot send messages to another users conversation', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $conversation = Conversation::factory()->create([
        'user_id' => $user1->id,
    ]);

    DevBot::fake(['AI response']);

    $response = $this->actingAs($user2)->postJson(route('chat.messages.send'), [
        'message' => 'Unauthorized message',
        'conversation_id' => $conversation->id,
    ]);

    // Action silently creates new conversation instead of using unauthorized one
    $response->assertSuccessful();

    // Verify message was NOT added to user1's conversation
    $this->assertDatabaseMissing('messages', [
        'conversation_id' => $conversation->id,
        'content' => 'Unauthorized message',
    ]);
});

/**
 * Task 8.7: Test logout redirects to home page
 */
test('logout redirects to home page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect('/');
});

/**
 * Task 8.8: Test accessing /chat after logout redirects to login
 */
test('accessing chat after logout redirects to login', function () {
    $user = User::factory()->create();

    // Login and verify access
    $this->actingAs($user);
    $response = $this->get(route('chat.show'));
    $response->assertSuccessful();

    // Logout
    $this->post(route('logout'));
    $this->assertGuest();

    // Try to access chat
    $response = $this->get(route('chat.show'));
    $response->assertRedirect(route('login'));
});

/**
 * Task 8.9: Create second user and verify conversation isolation
 */
test('complete conversation isolation between users', function () {
    // Create two users
    $user1 = User::factory()->create([
        'email' => 'user1@example.com',
    ]);

    $user2 = User::factory()->create([
        'email' => 'user2@example.com',
    ]);

    // Create conversations for each user
    $user1Conv1 = Conversation::factory()->create([
        'user_id' => $user1->id,
        'title' => 'User 1 - Conversation 1',
    ]);

    $user1Conv2 = Conversation::factory()->create([
        'user_id' => $user1->id,
        'title' => 'User 1 - Conversation 2',
    ]);

    $user2Conv = Conversation::factory()->create([
        'user_id' => $user2->id,
        'title' => 'User 2 - Conversation',
    ]);

    // Add messages to conversations
    Message::factory()->create([
        'conversation_id' => $user1Conv1->id,
        'role' => 'user',
        'content' => 'User 1 message in Conv 1',
    ]);

    Message::factory()->create([
        'conversation_id' => $user2Conv->id,
        'role' => 'user',
        'content' => 'User 2 message',
    ]);

    // Verify user1 can only see their conversations
    $user1Conversations = Conversation::where('user_id', $user1->id)->get();
    expect($user1Conversations)->toHaveCount(2);
    expect($user1Conversations->pluck('id')->toArray())->toContain($user1Conv1->id, $user1Conv2->id);
    expect($user1Conversations->pluck('id')->toArray())->not->toContain($user2Conv->id);

    // Verify user2 can only see their conversations
    $user2Conversations = Conversation::where('user_id', $user2->id)->get();
    expect($user2Conversations)->toHaveCount(1);
    expect($user2Conversations->pluck('id')->toArray())->toContain($user2Conv->id);
    expect($user2Conversations->pluck('id')->toArray())->not->toContain($user1Conv1->id, $user1Conv2->id);

    // Verify user1 cannot access user2's conversation (returns 404 for security)
    $response = $this->actingAs($user1)->getJson(route('chat.conversations.get', $user2Conv));
    $response->assertNotFound();

    // Verify user2 cannot access user1's conversation (returns 404 for security)
    $response = $this->actingAs($user2)->getJson(route('chat.conversations.get', $user1Conv1));
    $response->assertNotFound();

    // Verify messages are isolated
    $user1Messages = Message::whereIn('conversation_id', $user1Conversations->pluck('id'))->get();
    $user2Messages = Message::whereIn('conversation_id', $user2Conversations->pluck('id'))->get();

    expect($user1Messages)->toHaveCount(1);
    expect($user2Messages)->toHaveCount(1);
    expect($user1Messages->first()->content)->toBe('User 1 message in Conv 1');
    expect($user2Messages->first()->content)->toBe('User 2 message');
});

/**
 * Additional Integration Test: Full workflow
 */
test('complete user workflow from registration to messaging', function () {
    // Step 1: Register
    $response = $this->post(route('register'), [
        'name' => 'Full Workflow User',
        'email' => 'workflow@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $user = User::where('email', 'workflow@example.com')->first();
    expect($user)->not->toBeNull();

    // Step 2: Create conversation
    $response = $this->actingAs($user)->postJson(route('chat.conversations.create'));
    $response->assertSuccessful();

    $conversation = Conversation::where('user_id', $user->id)->first();
    expect($conversation)->not->toBeNull();

    // Step 3: Send message
    DevBot::fake(['AI response']);

    $response = $this->actingAs($user)->postJson(route('chat.messages.send'), [
        'message' => 'Hello AI!',
        'conversation_id' => $conversation->id,
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'content' => 'Hello AI!',
    ]);

    // Step 4: View conversation list
    $response = $this->actingAs($user)->get(route('chat.show'));
    $response->assertSuccessful();
    // Conversation title is generated from first message
    $response->assertSee('Hello AI!');

    // Step 5: Logout
    $response = $this->actingAs($user)->post(route('logout'));
    $this->assertGuest();

    // Step 6: Verify cannot access after logout
    $response = $this->get(route('chat.show'));
    $response->assertRedirect(route('login'));
});
