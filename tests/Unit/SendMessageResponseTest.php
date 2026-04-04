<?php

use App\DTOs\SendMessageResponse;
use App\Models\Conversation;
use App\Models\Message;

test('send message response can be instantiated with constructor', function () {
    $conversation = Mockery::mock(Conversation::class)->makePartial();
    $message = Mockery::mock(Message::class)->makePartial();

    $response = new SendMessageResponse(
        conversation: $conversation,
        assistantMessage: $message,
        success: true,
    );

    expect($response->conversation)->toBeInstanceOf(Conversation::class);
    expect($response->assistantMessage)->toBeInstanceOf(Message::class);
    expect($response->success)->toBeTrue();
    expect($response->errorMessage)->toBeNull();
});

test('send message response is successful returns true for success', function () {
    $conversation = Mockery::mock(Conversation::class)->makePartial();
    $message = Mockery::mock(Message::class)->makePartial();

    $response = new SendMessageResponse(
        conversation: $conversation,
        assistantMessage: $message,
        success: true,
    );

    expect($response->isSuccessful())->toBeTrue();
});

test('send message response is successful returns false for failure', function () {
    $conversation = Mockery::mock(Conversation::class)->makePartial();
    $message = Mockery::mock(Message::class)->makePartial();

    $response = new SendMessageResponse(
        conversation: $conversation,
        assistantMessage: $message,
        success: false,
        errorMessage: 'AI service unavailable',
    );

    expect($response->isSuccessful())->toBeFalse();
});

test('send message response to json data returns success format', function () {
    $conversation = Mockery::mock(Conversation::class)->makePartial();
    $conversation->id = 123;
    $conversation->title = 'Test Chat';

    $message = Mockery::mock(Message::class)->makePartial();
    $message->shouldReceive('formattedContent')->andReturn('Hello!');

    $response = new SendMessageResponse(
        conversation: $conversation,
        assistantMessage: $message,
        success: true,
    );

    $data = $response->toJsonData();

    expect($data)->toBe([
        'success' => true,
        'response' => 'Hello!',
        'conversation_id' => 123,
        'conversation_title' => 'Test Chat',
    ]);
});

test('send message response to json data returns error format', function () {
    $conversation = Mockery::mock(Conversation::class)->makePartial();
    $message = Mockery::mock(Message::class)->makePartial();

    $response = new SendMessageResponse(
        conversation: $conversation,
        assistantMessage: $message,
        success: false,
        errorMessage: 'Service timeout',
    );

    $data = $response->toJsonData();

    expect($data)->toBe([
        'success' => false,
        'message' => 'Service timeout',
    ]);
});

test('send message response to json data uses default error message', function () {
    $conversation = Mockery::mock(Conversation::class)->makePartial();
    $message = Mockery::mock(Message::class)->makePartial();

    $response = new SendMessageResponse(
        conversation: $conversation,
        assistantMessage: $message,
        success: false,
    );

    $data = $response->toJsonData();

    expect($data['message'])->toBe('An error occurred processing your message.');
});

test('send message response get error message returns error string', function () {
    $conversation = Mockery::mock(Conversation::class)->makePartial();
    $message = Mockery::mock(Message::class)->makePartial();

    $response = new SendMessageResponse(
        conversation: $conversation,
        assistantMessage: $message,
        success: false,
        errorMessage: 'Custom error',
    );

    expect($response->getErrorMessage())->toBe('Custom error');
});

test('send message response get error message returns null for success', function () {
    $conversation = Mockery::mock(Conversation::class)->makePartial();
    $message = Mockery::mock(Message::class)->makePartial();

    $response = new SendMessageResponse(
        conversation: $conversation,
        assistantMessage: $message,
        success: true,
    );

    expect($response->getErrorMessage())->toBeNull();
});

test('send message response success factory method', function () {
    $conversation = Mockery::mock(Conversation::class)->makePartial();
    $message = Mockery::mock(Message::class)->makePartial();

    $response = SendMessageResponse::success($conversation, $message);

    expect($response->isSuccessful())->toBeTrue();
    expect($response->conversation)->toBe($conversation);
    expect($response->assistantMessage)->toBe($message);
    expect($response->errorMessage)->toBeNull();
});

test('send message response failure factory method', function () {
    $response = SendMessageResponse::failure('AI API error');

    expect($response->isSuccessful())->toBeFalse();
    expect($response->errorMessage)->toBe('AI API error');
    expect($response->conversation)->toBeInstanceOf(Conversation::class);
    expect($response->assistantMessage)->toBeInstanceOf(Message::class);
});

test('send message response has readonly properties', function () {
    $conversation = Mockery::mock(Conversation::class)->makePartial();
    $message = Mockery::mock(Message::class)->makePartial();

    $response = new SendMessageResponse(
        conversation: $conversation,
        assistantMessage: $message,
        success: true,
    );

    expect($response)->toHaveProperty('conversation');
    expect($response)->toHaveProperty('assistantMessage');
    expect($response)->toHaveProperty('success');
    expect($response)->toHaveProperty('errorMessage');
});
