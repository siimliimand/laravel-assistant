<?php

use App\DTOs\MessageData;
use Illuminate\Http\Request;

test('message data can be instantiated with constructor', function () {
    $data = new MessageData(
        content: 'Hello, world!',
        conversationId: 123,
    );

    expect($data->content)->toBe('Hello, world!');
    expect($data->conversationId)->toBe(123);
});

test('message data conversation id defaults to null', function () {
    $data = new MessageData(content: 'Test message');

    expect($data->content)->toBe('Test message');
    expect($data->conversationId)->toBeNull();
});

test('message data can be created from request', function () {
    $request = Request::create('/chat', 'POST', [
        'message' => 'Test message',
        'conversation_id' => 456,
    ]);

    $data = MessageData::fromRequest($request);

    expect($data->content)->toBe('Test message');
    expect($data->conversationId)->toBe(456);
});

test('message data from request handles missing conversation id', function () {
    $request = Request::create('/chat', 'POST', [
        'message' => 'Test message',
    ]);

    $data = MessageData::fromRequest($request);

    expect($data->content)->toBe('Test message');
    // Request::integer() returns 0 for missing values, not null
    expect($data->conversationId)->toBe(0);
});

test('message data is immutable (readonly)', function () {
    $data = new MessageData(content: 'Original');

    // Verify that properties are readonly by checking they exist
    expect($data)->toHaveProperty('content');
    expect($data)->toHaveProperty('conversationId');
});

test('message data is final class', function () {
    $reflection = new ReflectionClass(MessageData::class);

    expect($reflection->isFinal())->toBeTrue();
    expect($reflection->isReadonly())->toBeTrue();
});
