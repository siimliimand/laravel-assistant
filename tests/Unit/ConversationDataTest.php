<?php

use App\DTOs\ConversationData;
use Illuminate\Http\Request;

test('conversation data can be instantiated with constructor', function () {
    $data = new ConversationData(
        title: 'My Chat',
        initialMessage: 'Hello!',
    );

    expect($data->title)->toBe('My Chat');
    expect($data->initialMessage)->toBe('Hello!');
});

test('conversation data title defaults to null', function () {
    $data = new ConversationData(initialMessage: 'Hello!');

    expect($data->title)->toBeNull();
    expect($data->initialMessage)->toBe('Hello!');
});

test('conversation data initial message defaults to null', function () {
    $data = new ConversationData(title: 'My Chat');

    expect($data->title)->toBe('My Chat');
    expect($data->initialMessage)->toBeNull();
});

test('conversation data can be created from request', function () {
    $request = Request::create('/chat', 'POST', [
        'title' => 'Test Conversation',
        'message' => 'Initial message',
    ]);

    $data = ConversationData::fromRequest($request);

    expect($data->title)->toBe('Test Conversation');
    expect($data->initialMessage)->toBe('Initial message');
});

test('conversation data can be created from message', function () {
    $data = ConversationData::fromMessage('Hello, world!');

    expect($data->title)->toBeNull();
    expect($data->initialMessage)->toBe('Hello, world!');
});

test('conversation data is immutable (readonly)', function () {
    $data = new ConversationData(title: 'Test');

    expect($data)->toHaveProperty('title');
    expect($data)->toHaveProperty('initialMessage');
});

test('conversation data is final class', function () {
    $reflection = new ReflectionClass(ConversationData::class);

    expect($reflection->isFinal())->toBeTrue();
    expect($reflection->isReadonly())->toBeTrue();
});
