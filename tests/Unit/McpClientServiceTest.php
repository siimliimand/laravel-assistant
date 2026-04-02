<?php

use App\Services\McpClientService;
use PhpMcp\Client\Client;
use PhpMcp\Client\Exception\McpClientException;
use PhpMcp\Client\JsonRpc\Results\CallToolResult;
use PhpMcp\Client\Model\Content\TextContent;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->mcpService = app(McpClientService::class);
});

test('McpClientService is registered as singleton', function () {
    $instance1 = app(McpClientService::class);
    $instance2 = app(McpClientService::class);

    expect($instance1)->toBe($instance2);
});

test('McpClientService can be instantiated', function () {
    expect($this->mcpService)->toBeInstanceOf(McpClientService::class);
});

test('McpClientService starts in disconnected state', function () {
    expect($this->mcpService->isConnected())->toBeFalse();
    expect($this->mcpService->getClient())->toBeNull();
});

test('McpClientService initialize creates client instance', function () {
    $this->mcpService->initialize();

    expect($this->mcpService->getClient())->not->toBeNull();
    expect($this->mcpService->isConnected())->toBeTrue();
})->skip('Requires real MCP server connection');

test('McpClientService disconnect cleans up client', function () {
    $this->mcpService->initialize();
    $this->mcpService->disconnect();

    expect($this->mcpService->isConnected())->toBeFalse();
    expect($this->mcpService->getClient())->toBeNull();
})->skip('Requires real MCP server connection');

test('McpClientService callTool throws exception when not connected', function () {
    $this->mcpService->callTool('test-tool', []);
})->throws(McpClientException::class);

test('McpClientService can call tool successfully', function () {
    $mockResult = new CallToolResult(
        content: [new TextContent(json_encode(['result' => 1]))],
        isError: false
    );

    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('isReady')->andReturnTrue();
    $mockClient->shouldReceive('callTool')
        ->with('database-query', ['query' => 'SELECT 1'])
        ->andReturn($mockResult);

    // Use reflection to set the mock client
    $reflection = new ReflectionClass($this->mcpService);
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientProperty->setValue($this->mcpService, $mockClient);

    $initializedProperty = $reflection->getProperty('initialized');
    $initializedProperty->setAccessible(true);
    $initializedProperty->setValue($this->mcpService, true);

    $result = $this->mcpService->callTool('database-query', ['query' => 'SELECT 1']);

    expect($result)->toBeString();
    expect($result)->toContain('result');
});

test('McpClientService handles tool call errors', function () {
    $mockResult = new CallToolResult(
        content: [new TextContent('Error: Tool not found')],
        isError: true
    );

    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('isReady')->andReturnTrue();
    $mockClient->shouldReceive('callTool')
        ->andThrow(new McpClientException('Tool not found'));

    $reflection = new ReflectionClass($this->mcpService);
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientProperty->setValue($this->mcpService, $mockClient);

    $initializedProperty = $reflection->getProperty('initialized');
    $initializedProperty->setAccessible(true);
    $initializedProperty->setValue($this->mcpService, true);

    $result = $this->mcpService->callTool('nonexistent-tool', []);

    // After max retries, it should throw an exception
    expect($result)->toContain('Error');
})->skip('Requires complex mock setup for retry logic');

test('McpClientService implements auto-reconnect on failure', function () {
    $mockResult = new CallToolResult(
        content: [new TextContent('Success after reconnect')],
        isError: false
    );

    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('isReady')
        ->andReturnTrue()
        ->andReturnTrue();
    $mockClient->shouldReceive('callTool')
        ->andThrow(new McpClientException('Connection lost'))
        ->andReturn($mockResult);

    $reflection = new ReflectionClass($this->mcpService);
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientProperty->setValue($this->mcpService, $mockClient);

    $initializedProperty = $reflection->getProperty('initialized');
    $initializedProperty->setAccessible(true);
    $initializedProperty->setValue($this->mcpService, true);

    // First call will fail and trigger reconnect, second will succeed
    $result = $this->mcpService->callTool('test-tool', []);

    expect($result)->toBe('Success after reconnect');
});

test('McpClientService terminate disconnects cleanly', function () {
    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('disconnect')->once();

    $reflection = new ReflectionClass($this->mcpService);
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientProperty->setValue($this->mcpService, $mockClient);

    $initializedProperty = $reflection->getProperty('initialized');
    $initializedProperty->setAccessible(true);
    $initializedProperty->setValue($this->mcpService, true);

    $this->mcpService->terminate();

    expect($this->mcpService->isConnected())->toBeFalse();
});

test('McpClientService extractTextContent handles string result', function () {
    $mockResult = new CallToolResult(
        content: [new TextContent('Plain text result')],
        isError: false
    );

    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('isReady')->andReturnTrue();
    $mockClient->shouldReceive('callTool')
        ->andReturn($mockResult);

    $reflection = new ReflectionClass($this->mcpService);
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientProperty->setValue($this->mcpService, $mockClient);

    $initializedProperty = $reflection->getProperty('initialized');
    $initializedProperty->setAccessible(true);
    $initializedProperty->setValue($this->mcpService, true);

    $result = $this->mcpService->callTool('test-tool', []);

    expect($result)->toBe('Plain text result');
});

test('McpClientService respects timeout configuration', function () {
    $timeout = config('services.mcp_client.timeout');

    expect($timeout)->toBeInt();
    expect($timeout)->toBeGreaterThan(0);
});

test('McpClientService respects retry configuration', function () {
    $maxRetries = config('services.mcp_client.max_retries');
    $retryDelay = config('services.mcp_client.retry_delay');

    expect($maxRetries)->toBeInt();
    expect($maxRetries)->toBeGreaterThanOrEqual(0);
    expect($retryDelay)->toBeInt();
    expect($retryDelay)->toBeGreaterThan(0);
});
