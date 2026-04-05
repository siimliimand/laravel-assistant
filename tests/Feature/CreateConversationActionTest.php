<?php

use App\Actions\CreateConversationAction;
use App\DTOs\ConversationData;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('create conversation action creates conversation with title', function () {
    $action = app(CreateConversationAction::class);
    $data = new ConversationData(title: 'My Chat');

    $conversation = $action->execute($data);

    expect($conversation)->toBeInstanceOf(Conversation::class);
    expect($conversation->title)->toBe('My Chat');
    expect($conversation->id)->not->toBeNull();
});

test('create conversation action creates conversation with default title', function () {
    $action = app(CreateConversationAction::class);
    $data = new ConversationData;

    $conversation = $action->execute($data);

    expect($conversation->title)->toBe('New Chat');
});

test('create conversation action generates title from initial message', function () {
    $action = app(CreateConversationAction::class);
    $data = new ConversationData(initialMessage: 'What is Laravel?');

    $conversation = $action->execute($data);

    expect($conversation->title)->not->toBe('New Chat');
    expect($conversation->title)->toBeString();
    expect(strlen($conversation->title))->toBeGreaterThan(0);
});

test('create conversation action uses provided title over initial message', function () {
    $action = app(CreateConversationAction::class);
    $data = new ConversationData(
        title: 'Custom Title',
        initialMessage: 'What is Laravel?'
    );

    $conversation = $action->execute($data);

    expect($conversation->title)->toBe('Custom Title');
});

test('create conversation action persists conversation to database', function () {
    $action = app(CreateConversationAction::class);
    $data = new ConversationData(title: 'Test Conversation');

    $conversation = $action->execute($data);

    $this->assertDatabaseHas('conversations', [
        'id' => $conversation->id,
        'title' => 'Test Conversation',
    ]);
});

test('create conversation action associates conversation with authenticated user', function () {
    $action = app(CreateConversationAction::class);
    $data = new ConversationData(title: 'User Conversation');

    $conversation = $action->execute($data);

    expect($conversation->user_id)->toBe($this->user->id);

    $this->assertDatabaseHas('conversations', [
        'id' => $conversation->id,
        'user_id' => $this->user->id,
    ]);
});
