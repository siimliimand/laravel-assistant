<?php

use App\Actions\GetConversationAction;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('get conversation action returns conversation with messages', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test Chat']);

    Message::factory()->userMessage('Hello')
        ->create(['conversation_id' => $conversation->id]);

    Message::factory()->assistantMessage('Hi there!')
        ->create(['conversation_id' => $conversation->id]);

    $action = app(GetConversationAction::class);
    $result = $action->execute($conversation->id);

    expect($result)->not->toBeNull();
    expect($result->id)->toBe($conversation->id);
    expect($result->messages)->toHaveCount(2);
});

test('get conversation action returns null for non-existent conversation', function () {
    $action = app(GetConversationAction::class);
    $result = $action->execute(99999);

    expect($result)->toBeNull();
});

test('get conversation action eager loads messages', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test Chat']);

    Message::factory()->userMessage('Test message')
        ->create(['conversation_id' => $conversation->id]);

    $action = app(GetConversationAction::class);
    $result = $action->execute($conversation->id);

    // Verify messages are loaded (no additional queries)
    expect($result->relationLoaded('messages'))->toBeTrue();
});

test('get conversation action orders messages by created_at ascending', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test Chat']);

    Message::factory()->userMessage('First')
        ->create([
            'conversation_id' => $conversation->id,
            'created_at' => now()->subMinutes(10),
        ]);

    Message::factory()->assistantMessage('Second')
        ->create([
            'conversation_id' => $conversation->id,
            'created_at' => now()->subMinutes(5),
        ]);

    $action = app(GetConversationAction::class);
    $result = $action->execute($conversation->id);

    expect($result->messages[0]->content)->toBe('First');
    expect($result->messages[1]->content)->toBe('Second');
});

test('get conversation action handles conversation with no messages', function () {
    $conversation = Conversation::factory()->create(['title' => 'Empty Chat']);

    $action = app(GetConversationAction::class);
    $result = $action->execute($conversation->id);

    expect($result)->not->toBeNull();
    expect($result->messages)->toHaveCount(0);
});

test('get conversation action returns null for conversation owned by another user', function () {
    $otherUser = User::factory()->create();
    $conversation = Conversation::factory()->create([
        'title' => 'Other User Chat',
        'user_id' => $otherUser->id,
    ]);

    $action = app(GetConversationAction::class);
    $result = $action->execute($conversation->id);

    expect($result)->toBeNull();
});

test('get conversation action only returns conversations owned by authenticated user', function () {
    $conversation = Conversation::factory()->create([
        'title' => 'My Chat',
        'user_id' => $this->user->id,
    ]);

    $action = app(GetConversationAction::class);
    $result = $action->execute($conversation->id);

    expect($result)->not->toBeNull();
    expect($result->id)->toBe($conversation->id);
    expect($result->user_id)->toBe($this->user->id);
});
