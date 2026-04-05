<?php

use App\Actions\SendMessageAction;
use App\Ai\Agents\DevBot;
use App\DTOs\MessageData;
use App\DTOs\SendMessageResponse;
use App\Exceptions\AiApiException;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test user and authenticate
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create action with DevBot factory for testing
    $this->action = new SendMessageAction(
        fn ($conversation) => new DevBot($conversation)
    );
});

test('send message action sends message and returns response', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test Chat', 'user_id' => $this->user->id]);
    $data = new MessageData(
        content: 'Hello, AI!',
        conversationId: $conversation->id,
    );

    // Mock DevBot using Laravel AI's fake method
    DevBot::fake(['Hello, human!']);

    $result = $this->action->execute($data);

    expect($result)->toBeInstanceOf(SendMessageResponse::class);
    expect($result->isSuccessful())->toBeTrue();
    expect($result->conversation->id)->toBe($conversation->id);
    expect($result->assistantMessage->content)->toBe('Hello, human!');

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'content' => 'Hello, AI!',
    ]);
});

test('send message action creates new conversation when id is null', function () {
    $data = new MessageData(
        content: 'Start new conversation',
        conversationId: null,
    );

    DevBot::fake(['Response']);

    $result = $this->action->execute($data);

    expect($result->isSuccessful())->toBeTrue();
    expect($result->conversation->id)->not->toBeNull();
    expect($result->conversation->title)->not->toBe('New Chat');
    expect($result->conversation->user_id)->toBe($this->user->id);

    $this->assertDatabaseCount('conversations', 1);
    $this->assertDatabaseCount('messages', 2); // user + assistant
});

test('send message action saves both user and assistant messages', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test', 'user_id' => $this->user->id]);
    $data = new MessageData(
        content: 'User message',
        conversationId: $conversation->id,
    );

    DevBot::fake(['AI response']);

    $this->action->execute($data);

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'content' => 'User message',
    ]);

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'content' => 'AI response',
    ]);
});

test('send message action wraps AI API exception with context', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test Chat', 'user_id' => $this->user->id]);
    $data = new MessageData(
        content: 'This will fail',
        conversationId: $conversation->id,
    );

    // Mock DevBot to throw an exception
    DevBot::fake(fn () => throw new Exception('AI API timeout'));

    expect(fn () => $this->action->execute($data))
        ->toThrow(AiApiException::class, 'Failed to get AI response: AI API timeout');

    // Verify user message was still saved
    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'content' => 'This will fail',
    ]);

    // Verify assistant message was NOT saved
    $assistantMessages = Message::where('conversation_id', $conversation->id)
        ->where('content', '!=', 'This will fail')
        ->count();
    expect($assistantMessages)->toBe(0);
});

test('send message action exception includes conversation ID context', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test', 'user_id' => $this->user->id]);
    $data = new MessageData(
        content: 'Test message',
        conversationId: $conversation->id,
    );

    DevBot::fake(fn () => throw new Exception('API Error'));

    try {
        $this->action->execute($data);
    } catch (AiApiException $e) {
        expect($e->conversationId)->toBe($conversation->id);
        expect($e->getPrevious())->toBeInstanceOf(Exception::class);
        expect($e->getPrevious()->getMessage())->toBe('API Error');
    }
});

test('send message action exception provides context array', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test', 'user_id' => $this->user->id]);
    $data = new MessageData(
        content: 'Test',
        conversationId: $conversation->id,
    );

    DevBot::fake(fn () => throw new Exception('Original error'));

    try {
        $this->action->execute($data);
    } catch (AiApiException $e) {
        $context = $e->context();

        expect($context)->toHaveKey('conversation_id', $conversation->id);
        expect($context)->toHaveKey('user_id', $this->user->id);
        expect($context['original_exception'])->toBe('Exception');
        expect($context['original_message'])->toBe('Original error');
    }
});

test('send message action logs error when AI API fails', function () {
    Log::shouldReceive('error')
        ->once()
        ->with(Mockery::type('string'), Mockery::on(function ($context) {
            return isset($context['conversation_id']) &&
                   $context['exception'] instanceof AiApiException;
        }));

    $conversation = Conversation::factory()->create(['title' => 'Test', 'user_id' => $this->user->id]);
    $data = new MessageData(
        content: 'Test',
        conversationId: $conversation->id,
    );

    DevBot::fake(fn () => throw new Exception('API Error'));

    try {
        $this->action->execute($data);
    } catch (AiApiException) {
        // Expected
    }
});

test('send message action preserves original exception chain', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test', 'user_id' => $this->user->id]);
    $data = new MessageData(
        content: 'Test',
        conversationId: $conversation->id,
    );

    $originalException = new RuntimeException('Connection refused', 500);
    DevBot::fake(fn () => throw $originalException);

    try {
        $this->action->execute($data);
    } catch (AiApiException $e) {
        expect($e->getPrevious())->toBe($originalException);
        expect($e->getCode())->toBe(500);
        expect($e->getMessage())->toContain('Connection refused');
    }
});

test('send message action handles nested exceptions correctly', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test', 'user_id' => $this->user->id]);
    $data = new MessageData(
        content: 'Test',
        conversationId: $conversation->id,
    );

    // Create a nested exception scenario
    $innerException = new Exception('Network error');
    $originalException = new RuntimeException('API call failed', 0, $innerException);

    DevBot::fake(fn () => throw $originalException);

    try {
        $this->action->execute($data);
    } catch (AiApiException $e) {
        expect($e->getPrevious())->toBe($originalException);
        expect($e->getPrevious()->getPrevious())->toBe($innerException);
        expect($e->context()['original_exception'])->toBe('RuntimeException');
    }
});
