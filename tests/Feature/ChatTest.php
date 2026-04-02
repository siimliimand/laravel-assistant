<?php

use App\Ai\Agents\DevBot;
use App\Ai\Tools\DatabaseQueryTool;
use App\Ai\Tools\DatabaseSchemaTool;
use App\Ai\Tools\SearchDocsTool;
use App\Ai\Tools\TinkerTool;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\McpClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test conversation for reuse
    $this->conversation = Conversation::create([
        'title' => 'Test Conversation',
    ]);
});

/**
 * ==========================================
 * Chat Interface Display Tests
 * ==========================================
 */
test('chat page loads successfully', function () {
    $response = $this->get(route('chat.show'));

    $response->assertSuccessful();
    $response->assertViewIs('chat');
    $response->assertViewHas('conversation');
    $response->assertViewHas('messages');
});

test('chat page shows welcome message when no messages exist', function () {
    $response = $this->get(route('chat.show'));

    $response->assertSuccessful();
    $response->assertSee('DevBot');
    $response->assertSee('What would you like help with today?');
});

test('chat page displays conversation when provided', function () {
    $response = $this->get(route('chat.show', ['conversation' => $this->conversation]));

    $response->assertSuccessful();
    $response->assertViewHas('conversation', $this->conversation);
    $response->assertSee('Test Conversation');
});

test('chat page displays existing messages', function () {
    Message::create([
        'conversation_id' => $this->conversation->id,
        'role' => 'user',
        'content' => 'Hello, DevBot!',
    ]);

    Message::create([
        'conversation_id' => $this->conversation->id,
        'role' => 'assistant',
        'content' => 'Hello! How can I help you today?',
    ]);

    $response = $this->get(route('chat.show', ['conversation' => $this->conversation]));

    $response->assertSuccessful();
    $response->assertSee('Hello, DevBot!');
    $response->assertSee('Hello! How can I help you today?');
});

test('chat page loads most recent conversation when no conversation specified', function () {
    $recentConversation = Conversation::create([
        'title' => 'Recent Chat',
    ]);

    $response = $this->get(route('chat.show'));

    $response->assertSuccessful();
    $response->assertViewHas('conversation', $recentConversation);
});

/**
 * ==========================================
 * Message Sending Tests
 * Note: Tests that require actual AI responses are marked with @group ai
 * and can be skipped when AI is not available
 * ==========================================
 */
test('can send message and receive AI response via AJAX', function () {
    // This test requires actual AI API access or proper Prism mocking
    // Skipping for now - integration tests should run with real API
    $this->markTestSkipped('Requires AI API access or Prism mocking');

    // Mock the AI response
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [
                ['type' => 'text', 'text' => 'This is a test response from DevBot'],
            ],
        ], 200),
    ]);

    $response = $this->postJson(route('chat.message'), [
        'message' => 'What is Laravel?',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
    ]);
    $response->assertJsonPath('response', fn ($json) => $json !== null);
    $response->assertJsonStructure([
        'success',
        'response',
        'conversation_id',
    ]);

    // Verify conversation was created
    $this->assertDatabaseHas('conversations', [
        'title' => 'What is Laravel?',
    ]);

    // Verify messages were saved
    $this->assertDatabaseHas('messages', [
        'role' => 'user',
        'content' => 'What is Laravel?',
    ]);
});

test('can send message to existing conversation', function () {
    $this->markTestSkipped('Requires AI API access or Prism mocking');

    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [
                ['type' => 'text', 'text' => 'Follow-up response'],
            ],
        ], 200),
    ]);

    $response = $this->postJson(route('chat.message'), [
        'message' => 'Follow-up question',
        'conversation_id' => $this->conversation->id,
    ]);

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);
    $response->assertJsonPath('conversation_id', $this->conversation->id);

    // Verify message was added to existing conversation
    $this->assertDatabaseHas('messages', [
        'conversation_id' => $this->conversation->id,
        'role' => 'user',
        'content' => 'Follow-up question',
    ]);
});

test('user message is saved before AI response', function () {
    DevBot::fake(['AI response']);

    $this->postJson(route('chat.message'), [
        'message' => 'Test message order',
    ]);

    // User message should exist
    $userMessage = Message::where('role', 'user')->first();
    expect($userMessage)->not->toBeNull();
    expect($userMessage->content)->toBe('Test message order');

    // Assistant message should also exist
    $assistantMessage = Message::where('role', 'assistant')->first();
    expect($assistantMessage)->not->toBeNull();
    expect($assistantMessage->content)->toBe('AI response');
});

/**
 * ==========================================
 * Validation Tests
 * ==========================================
 */
test('message is required', function () {
    $response = $this->postJson(route('chat.message'), [
        'message' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['message']);
});

test('message must be a string', function () {
    $response = $this->postJson(route('chat.message'), [
        'message' => 123,
    ]);

    // Laravel validates that the message must be a string
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['message']);
});

test('message has minimum length of 1', function () {
    $response = $this->postJson(route('chat.message'), [
        'message' => '   ',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['message']);
});

test('message has maximum length of 5000', function () {
    $longMessage = str_repeat('a', 5001);

    $response = $this->postJson(route('chat.message'), [
        'message' => $longMessage,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['message']);
});

test('conversation_id must exist if provided', function () {
    $response = $this->postJson(route('chat.message'), [
        'message' => 'Test message',
        'conversation_id' => 99999,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['conversation_id']);
});

test('conversation_id is optional', function () {
    DevBot::fake(['Response']);

    // Should work without conversation_id
    $response = $this->postJson(route('chat.message'), [
        'message' => 'New conversation',
    ]);

    $response->assertSuccessful();
});

/**
 * ==========================================
 * Conversation Creation Tests
 * ==========================================
 */
test('new conversation is created when conversation_id not provided', function () {
    DevBot::fake(['Response']);

    $initialCount = Conversation::count();

    $response = $this->postJson(route('chat.message'), [
        'message' => 'Start new chat',
    ]);

    $response->assertSuccessful();
    expect(Conversation::count())->toBe($initialCount + 1);
});

test('conversation title is generated from first message', function () {
    DevBot::fake(['Response']);

    $this->postJson(route('chat.message'), [
        'message' => 'How do I create a Laravel controller?',
    ]);

    $this->assertDatabaseHas('conversations', [
        'title' => 'How do I create a Laravel controller?',
    ]);
});

test('conversation title is truncated to 50 characters', function () {
    DevBot::fake(['Response']);

    $longMessage = 'What is the best way to implement authentication in a Laravel application with multiple guards?';

    $this->postJson(route('chat.message'), [
        'message' => $longMessage,
    ]);

    $conversation = Conversation::latest()->first();
    expect(strlen($conversation->title))->toBeLessThanOrEqual(50);
});

test('existing conversation is reused when conversation_id provided', function () {
    DevBot::fake(['Response']);

    $initialCount = Conversation::count();

    $response = $this->postJson(route('chat.message'), [
        'message' => 'Second message',
        'conversation_id' => $this->conversation->id,
    ]);

    $response->assertSuccessful();
    expect(Conversation::count())->toBe($initialCount); // No new conversation created
});

test('conversation has default title before first message title generation', function () {
    // This tests the edge case where we create conversation with 'New Chat'
    // before the title is generated from the first message
    DevBot::fake(['Response']);

    $this->postJson(route('chat.message'), [
        'message' => 'First message',
    ]);

    // Title should be updated from 'New Chat' to the first message
    $this->assertDatabaseHas('conversations', [
        'title' => 'First message',
    ]);
});

/**
 * ==========================================
 * Error Handling Tests
 * ==========================================
 */
test('handles AI API failure gracefully', function () {
    // Mock API failure by throwing an exception from the fake
    DevBot::fake(fn () => throw new Exception('API Error'));

    $response = $this->postJson(route('chat.message'), [
        'message' => 'Test message',
    ]);

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
        'message' => 'Failed to get a response from DevBot. Please try again later.',
    ]);

    // User message should still be saved
    $this->assertDatabaseHas('messages', [
        'role' => 'user',
        'content' => 'Test message',
    ]);
});

test('handles network timeout', function () {
    DevBot::fake(fn () => throw new Exception('Network timeout'));

    $response = $this->postJson(route('chat.message'), [
        'message' => 'Test timeout',
    ]);

    $response->assertStatus(500);
    $response->assertJson(['success' => false]);
});

test('error is logged when API fails', function () {
    Log::shouldReceive('error')
        ->once()
        ->withArgs(function ($message, $context) {
            return str_contains($message, 'DevBot API error');
        });

    DevBot::fake(fn () => throw new Exception('Service Unavailable'));

    $this->postJson(route('chat.message'), [
        'message' => 'Test logging',
    ]);
});

/**
 * ==========================================
 * Response Format Tests
 * ==========================================
 */
test('AJAX request returns JSON response', function () {
    DevBot::fake(['JSON response']);

    $response = $this->postJson(route('chat.message'), [
        'message' => 'Test JSON',
    ]);

    $response->assertHeader('Content-Type', 'application/json');
    $response->assertJsonStructure([
        'success',
        'response',
        'conversation_id',
    ]);
});

test('regular POST request redirects', function () {
    DevBot::fake(['Redirect response']);

    $response = $this->post(route('chat.message'), [
        'message' => 'Test redirect',
    ]);

    $conversation = Conversation::latest()->first();
    $response->assertRedirect(route('chat.show', ['conversation' => $conversation]));
});

test('AJAX error returns JSON error', function () {
    DevBot::fake(fn () => throw new Exception('API Error'));

    $response = $this->postJson(route('chat.message'), [
        'message' => 'Test error',
    ]);

    $response->assertHeader('Content-Type', 'application/json');
    $response->assertJsonStructure([
        'success',
        'message',
    ]);
});

/**
 * ==========================================
 * Message Display and Formatting Tests
 * ==========================================
 */
test('messages are ordered by creation time ascending', function () {
    Message::create([
        'conversation_id' => $this->conversation->id,
        'role' => 'user',
        'content' => 'First message',
    ]);

    Message::create([
        'conversation_id' => $this->conversation->id,
        'role' => 'assistant',
        'content' => 'Second message',
    ]);

    Message::create([
        'conversation_id' => $this->conversation->id,
        'role' => 'user',
        'content' => 'Third message',
    ]);

    $response = $this->get(route('chat.show', ['conversation' => $this->conversation]));

    $response->assertSuccessful();

    // Check that messages appear in correct order in the HTML
    $content = $response->content();
    $firstPos = strpos($content, 'First message');
    $secondPos = strpos($content, 'Second message');
    $thirdPos = strpos($content, 'Third message');

    expect($firstPos)->toBeLessThan($secondPos);
    expect($secondPos)->toBeLessThan($thirdPos);
});

test('user messages are displayed with correct role', function () {
    Message::create([
        'conversation_id' => $this->conversation->id,
        'role' => 'user',
        'content' => 'User message',
    ]);

    $response = $this->get(route('chat.show', ['conversation' => $this->conversation]));

    $response->assertSuccessful();
    $response->assertSee('User message');
    $response->assertSee('You');
});

test('assistant messages are displayed with correct role', function () {
    Message::create([
        'conversation_id' => $this->conversation->id,
        'role' => 'assistant',
        'content' => 'Assistant message',
    ]);

    $response = $this->get(route('chat.show', ['conversation' => $this->conversation]));

    $response->assertSuccessful();
    $response->assertSee('Assistant message');
    $response->assertSee('DevBot');
});

/**
 * ==========================================
 * Conversation Model Tests
 * ==========================================
 */
test('conversation generates title from first message', function () {
    $conversation = Conversation::create([
        'title' => 'New Chat',
    ]);

    $conversation->generateTitleFromFirstMessage('How to build REST APIs?');

    $this->assertDatabaseHas('conversations', [
        'id' => $conversation->id,
        'title' => 'How to build REST APIs?',
    ]);
});

test('conversation title is truncated when too long', function () {
    $conversation = Conversation::create([
        'title' => 'New Chat',
    ]);

    $longMessage = str_repeat('a', 100);
    $conversation->generateTitleFromFirstMessage($longMessage);

    $this->assertDatabaseHas('conversations', [
        'id' => $conversation->id,
        'title' => str_repeat('a', 50),
    ]);
});

test('conversation retrieves recent messages with limit', function () {
    // Create 60 messages
    for ($i = 0; $i < 60; $i++) {
        Message::create([
            'conversation_id' => $this->conversation->id,
            'role' => $i % 2 === 0 ? 'user' : 'assistant',
            'content' => "Message {$i}",
        ]);
    }

    $recentMessages = $this->conversation->recentMessages;

    expect($recentMessages->count())->toBe(50);
    expect($recentMessages->first()->content)->toBe('Message 0');
});

test('conversation has many messages', function () {
    Message::create([
        'conversation_id' => $this->conversation->id,
        'role' => 'user',
        'content' => 'Test message 1',
    ]);

    Message::create([
        'conversation_id' => $this->conversation->id,
        'role' => 'assistant',
        'content' => 'Test message 2',
    ]);

    expect($this->conversation->messages->count())->toBe(2);
});

/**
 * ==========================================
 * Integration Tests
 * ==========================================
 */
test('full conversation flow: send multiple messages', function () {
    DevBot::fake(['Response 1', 'Response 2', 'Response 3']);

    // First message
    $response1 = $this->postJson(route('chat.message'), [
        'message' => 'Hello',
    ]);

    $response1->assertSuccessful();
    $conversationId = $response1->json('conversation_id');

    // Second message
    $response2 = $this->postJson(route('chat.message'), [
        'message' => 'How are you?',
        'conversation_id' => $conversationId,
    ]);

    $response2->assertSuccessful();

    // Third message
    $response3 = $this->postJson(route('chat.message'), [
        'message' => 'Tell me more',
        'conversation_id' => $conversationId,
    ]);

    $response3->assertSuccessful();

    // Verify all messages are in the same conversation
    $conversation = Conversation::find($conversationId);
    expect($conversation->messages->count())->toBe(6); // 3 user + 3 assistant
});

test('conversation persists across page reloads', function () {
    DevBot::fake(['Response']);

    // Send a message
    $response = $this->postJson(route('chat.message'), [
        'message' => 'Persistent message',
    ]);

    $conversationId = $response->json('conversation_id');

    // Reload the chat page with this conversation
    $pageResponse = $this->get(route('chat.show', ['conversation' => $conversationId]));

    $pageResponse->assertSuccessful();
    $pageResponse->assertSee('Persistent message');
    $pageResponse->assertSee('Response');
});

/**
 * ==========================================
 * MCP Tool Integration Tests
 * ==========================================
 */
test('DevBot has MCP tool proxies registered', function () {
    $devBot = new DevBot;
    $tools = iterator_to_array($devBot->tools());

    expect($tools)->toHaveCount(4);

    $toolClasses = array_map('get_class', $tools);
    expect($toolClasses)->toContain(DatabaseQueryTool::class);
    expect($toolClasses)->toContain(DatabaseSchemaTool::class);
    expect($toolClasses)->toContain(SearchDocsTool::class);
    expect($toolClasses)->toContain(TinkerTool::class);
});

test('DevBot tools implement Laravel AI Tool interface', function () {
    $devBot = new DevBot;
    $tools = iterator_to_array($devBot->tools());

    foreach ($tools as $tool) {
        expect($tool)->toBeInstanceOf(Tool::class);
    }
});

test('DevBot conversation with MCP tool call succeeds', function () {
    // This test requires real AI API and MCP server connection
    $this->markTestSkipped('Requires real AI API and MCP server connection');

    $conversation = Conversation::create(['title' => 'Tool Test']);
    Message::create([
        'conversation_id' => $conversation->id,
        'role' => 'user',
        'content' => 'What tables exist in the database?',
    ]);

    $devBot = new DevBot($conversation);
    $response = $devBot->prompt('What tables exist in the database?');

    expect($response->text)->toBeString();
})->skip('Requires real AI API and MCP server connection');

test('tool calls are tracked in conversation message history', function () {
    // This test requires real AI API and MCP server connection
    $this->markTestSkipped('Requires real AI API and MCP server connection');

    $conversation = Conversation::create(['title' => 'Tool History Test']);

    $devBot = new DevBot($conversation);
    $devBot->prompt('Execute SELECT 1');

    // Refresh conversation messages
    $conversation->load('messages');

    // Should have user message and assistant response
    expect($conversation->messages->count())->toBeGreaterThanOrEqual(2);
})->skip('Requires real AI API and MCP server connection');

test('multiple tool calls in single conversation turn', function () {
    // This test requires real AI API and MCP server connection
    $this->markTestSkipped('Requires real AI API and MCP server connection');

    $conversation = Conversation::create(['title' => 'Multi-Tool Test']);

    $devBot = new DevBot($conversation);
    $response = $devBot->prompt('Show me the database schema and then list the users table');

    expect($response->text)->toBeString();
})->skip('Requires real AI API and MCP server connection');

test('tool call error handling does not crash DevBot conversation', function () {
    DevBot::fake(['I encountered an error but I am still functioning.']);

    $response = $this->postJson(route('chat.message'), [
        'message' => 'Execute an invalid query',
    ]);

    $response->assertSuccessful();
    expect($response->json('success'))->toBeTrue();
});

test('MCP client service is available in container', function () {
    $mcpClient = app(McpClientService::class);

    expect($mcpClient)->toBeInstanceOf(McpClientService::class);
});

test('MCP client service is registered as singleton', function () {
    $instance1 = app(McpClientService::class);
    $instance2 = app(McpClientService::class);

    expect($instance1)->toBe($instance2);
});

/**
 * ==========================================
 * End-to-End Integration Tests (Requires Real MCP/AI)
 * ==========================================
 */
test('e2e: DevBot queries database via MCP tool', function () {
    // This test requires real AI API and MCP server connection
    $this->markTestSkipped('Requires real AI API and MCP server connection');

    $conversation = Conversation::create(['title' => 'E2E Database Query']);

    $devBot = new DevBot($conversation);
    $response = $devBot->prompt('Show me all tables in the database');

    expect($response->text)->toBeString();
    expect($response->text)->toContain('users');
})->skip('Requires real AI API and MCP server connection');

test('e2e: DevBot searches docs via MCP tool', function () {
    // This test requires real AI API and MCP server connection
    $this->markTestSkipped('Requires real AI API and MCP server connection');

    $conversation = Conversation::create(['title' => 'E2E Docs Search']);

    $devBot = new DevBot($conversation);
    $response = $devBot->prompt('Search for Eloquent relationship documentation');

    expect($response->text)->toBeString();
})->skip('Requires real AI API and MCP server connection');

test('e2e: DevBot executes PHP via MCP tinker tool', function () {
    // This test requires real AI API and MCP server connection
    $this->markTestSkipped('Requires real AI API and MCP server connection');

    $conversation = Conversation::create(['title' => 'E2E Tinker']);

    $devBot = new DevBot($conversation);
    $response = $devBot->prompt('Execute: return 2 + 2');

    expect($response->text)->toBeString();
    expect($response->text)->toContain('4');
})->skip('Requires real AI API and MCP server connection');

test('e2e: DevBot handles invalid SQL query error', function () {
    // This test requires real AI API and MCP server connection
    $this->markTestSkipped('Requires real AI API and MCP server connection');

    $conversation = Conversation::create(['title' => 'E2E Error Handling']);

    $devBot = new DevBot($conversation);
    $response = $devBot->prompt('Execute this SQL: SELECT * FROM nonexistent_table_xyz');

    // DevBot should handle the error gracefully and respond
    expect($response->text)->toBeString();
})->skip('Requires real AI API and MCP server connection');

test('e2e: DevBot handles PHP exception gracefully', function () {
    // This test requires real AI API and MCP server connection
    $this->markTestSkipped('Requires real AI API and MCP server connection');

    $conversation = Conversation::create(['title' => 'E2E Exception Handling']);

    $devBot = new DevBot($conversation);
    $response = $devBot->prompt('Execute this PHP: throw new Exception("Test exception")');

    // DevBot should handle the exception gracefully
    expect($response->text)->toBeString();
})->skip('Requires real AI API and MCP server connection');

test('e2e: MCP client reconnects after server crash', function () {
    // This test requires real MCP server connection
    $this->markTestSkipped('Requires real MCP server connection');

    $mcpClient = app(McpClientService::class);
    $mcpClient->initialize();

    // Simulate server crash by disconnecting
    $mcpClient->disconnect();

    // Next tool call should auto-reconnect
    $result = $mcpClient->callTool('database-schema', []);
    expect($result)->toBeString();
})->skip('Requires real MCP server connection');

test('e2e: MCP client handles timeout correctly', function () {
    // This test requires real MCP server connection
    $this->markTestSkipped('Requires real MCP server connection');

    $mcpClient = app(McpClientService::class);

    // Execute a command that might timeout
    $result = $mcpClient->callTool('tinker', ['code' => 'sleep(120); return 1;']);

    // Should handle timeout gracefully
    expect($result)->toBeString();
})->skip('Requires real MCP server connection');
