<?php

use App\Actions\GetConversationAction;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
