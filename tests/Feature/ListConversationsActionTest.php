<?php

use App\Actions\ListConversationsAction;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
