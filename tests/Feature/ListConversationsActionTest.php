<?php

use App\Actions\ListConversationsAction;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('list conversations action returns recent conversations', function () {
    Conversation::factory()->create(['title' => 'Old Chat', 'created_at' => now()->subDays(10)]);
    Conversation::factory()->create(['title' => 'Recent Chat', 'created_at' => now()->subDay()]);
    Conversation::factory()->create(['title' => 'Newest Chat', 'created_at' => now()]);

    $action = app(ListConversationsAction::class);
    $result = $action->execute();

    expect($result)->toHaveCount(3);
    expect($result->first()->title)->toBe('Newest Chat');
    expect($result->last()->title)->toBe('Old Chat');
});

test('list conversations action respects limit parameter', function () {
    for ($i = 0; $i < 10; $i++) {
        Conversation::factory()->create(['title' => "Chat {$i}"]);
    }

    $action = app(ListConversationsAction::class);
    $result = $action->execute(limit: 5);

    expect($result)->toHaveCount(5);
});

test('list conversations action returns empty collection when no conversations', function () {
    $action = app(ListConversationsAction::class);
    $result = $action->execute();

    expect($result)->toHaveCount(0);
});

test('list conversations action orders by latest first', function () {
    $old = Conversation::factory()->create(['title' => 'Old', 'created_at' => now()->subDays(5)]);
    $new = Conversation::factory()->create(['title' => 'New', 'created_at' => now()]);

    $action = app(ListConversationsAction::class);
    $result = $action->execute();

    expect($result->first()->id)->toBe($new->id);
    expect($result->last()->id)->toBe($old->id);
});

test('list conversations action uses default limit of 50', function () {
    for ($i = 0; $i < 60; $i++) {
        Conversation::factory()->create(['title' => "Chat {$i}"]);
    }

    $action = app(ListConversationsAction::class);
    $result = $action->execute();

    expect($result)->toHaveCount(50);
});

test('list conversations action only returns conversations for authenticated user', function () {
    // Create conversations for current user
    Conversation::factory()->create(['title' => 'My Chat 1', 'user_id' => $this->user->id]);
    Conversation::factory()->create(['title' => 'My Chat 2', 'user_id' => $this->user->id]);

    // Create conversations for another user
    $otherUser = User::factory()->create();
    Conversation::factory()->create(['title' => 'Other Chat 1', 'user_id' => $otherUser->id]);
    Conversation::factory()->create(['title' => 'Other Chat 2', 'user_id' => $otherUser->id]);

    $action = app(ListConversationsAction::class);
    $result = $action->execute();

    expect($result)->toHaveCount(2);
    expect($result->pluck('title')->toArray())->toContain('My Chat 1', 'My Chat 2');
    expect($result->pluck('title')->toArray())->not->toContain('Other Chat 1', 'Other Chat 2');
});
