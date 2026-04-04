<?php

use App\Ai\Tools\DatabaseQueryTool;
use App\Ai\Tools\DatabaseSchemaTool;
use App\Ai\Tools\SearchDocsTool;
use App\Ai\Tools\TinkerTool;
use App\Services\McpClientService;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->mockMcpClient = Mockery::mock(McpClientService::class);
    app()->instance(McpClientService::class, $this->mockMcpClient);
});

// DatabaseQueryTool Tests

test('DatabaseQueryTool calls MCP client with correct arguments', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->once()
        ->with('database-query', ['query' => 'SELECT * FROM users'])
        ->andReturn(json_encode(['users' => []]));

    $tool = app(DatabaseQueryTool::class);
    $request = new Request(['query' => 'SELECT * FROM users']);
    $result = $tool->handle($request);

    expect($result)->toBeString();
});

test('DatabaseQueryTool validates read-only queries', function () {
    $tool = app(DatabaseQueryTool::class);
    $request = new Request(['query' => 'DELETE FROM users']);
    $result = $tool->handle($request);

    expect($result)->toContain('Error');
    expect($result)->toContain('read-only');
});

test('DatabaseQueryTool allows SELECT queries', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->andReturn('results');

    $tool = app(DatabaseQueryTool::class);
    $request = new Request(['query' => 'SELECT * FROM users']);
    $result = $tool->handle($request);

    expect($result)->toBe('results');
});

test('DatabaseQueryTool allows SHOW queries', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->andReturn('results');

    $tool = app(DatabaseQueryTool::class);
    $request = new Request(['query' => 'SHOW TABLES']);
    $result = $tool->handle($request);

    expect($result)->toBe('results');
});

test('DatabaseQueryTool passes database connection parameter', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->with('database-query', ['query' => 'SELECT 1', 'database' => 'testing'])
        ->andReturn('results');

    $tool = app(DatabaseQueryTool::class);
    $request = new Request(['query' => 'SELECT 1', 'database' => 'testing']);
    $result = $tool->handle($request);

    expect($result)->toBe('results');
});

test('DatabaseQueryTool has correct schema', function () {
    $tool = app(DatabaseQueryTool::class);
    $schema = $tool->schema(new JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('query');
    expect($schema)->toHaveKey('database');
});

// DatabaseSchemaTool Tests

test('DatabaseSchemaTool calls MCP client with no table', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->once()
        ->with('database-schema', [])
        ->andReturn(json_encode(['tables' => ['users']]));

    $tool = app(DatabaseSchemaTool::class);
    $request = new Request([]);
    $result = $tool->handle($request);

    expect($result)->toBeString();
});

test('DatabaseSchemaTool calls MCP client with table name', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->once()
        ->with('database-schema', ['table' => 'users'])
        ->andReturn(json_encode(['table' => 'users']));

    $tool = app(DatabaseSchemaTool::class);
    $request = new Request(['table' => 'users']);
    $result = $tool->handle($request);

    expect($result)->toBeString();
});

test('DatabaseSchemaTool passes database connection parameter', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->with('database-schema', ['table' => 'users', 'database' => 'testing'])
        ->andReturn('schema');

    $tool = app(DatabaseSchemaTool::class);
    $request = new Request(['table' => 'users', 'database' => 'testing']);
    $result = $tool->handle($request);

    expect($result)->toBe('schema');
});

test('DatabaseSchemaTool has correct schema', function () {
    $tool = app(DatabaseSchemaTool::class);
    $schema = $tool->schema(new JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('table');
    expect($schema)->toHaveKey('database');
});

// SearchDocsTool Tests

test('SearchDocsTool calls MCP client with correct arguments', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->once()
        ->with('search-docs', [
            'queries' => ['routing'],
            'token_limit' => 3000,
        ])
        ->andReturn(json_encode(['results' => []]));

    $tool = app(SearchDocsTool::class);
    $request = new Request(['queries' => ['routing']]);
    $result = $tool->handle($request);

    expect($result)->toBeString();
});

test('SearchDocsTool validates queries parameter', function () {
    $tool = app(SearchDocsTool::class);
    $request = new Request(['queries' => []]);
    $result = $tool->handle($request);

    expect($result)->toContain('Error');
    expect($result)->toContain('queries');
});

test('SearchDocsTool passes packages parameter', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->with('search-docs', [
            'queries' => ['eloquent'],
            'packages' => ['laravel/framework'],
            'token_limit' => 3000,
        ])
        ->andReturn('docs');

    $tool = app(SearchDocsTool::class);
    $request = new Request([
        'queries' => ['eloquent'],
        'packages' => ['laravel/framework'],
    ]);
    $result = $tool->handle($request);

    expect($result)->toBe('docs');
});

test('SearchDocsTool has correct schema', function () {
    $tool = app(SearchDocsTool::class);
    $schema = $tool->schema(new JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('queries');
    expect($schema)->toHaveKey('packages');
    expect($schema)->toHaveKey('token_limit');
});

// TinkerTool Tests

test('TinkerTool calls MCP client with correct arguments', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->once()
        ->with('tinker', [
            'code' => 'return User::count();',
            'timeout' => 30,
        ])
        ->andReturn(json_encode(['output' => '', 'return' => 10]));

    $tool = app(TinkerTool::class);
    $request = new Request(['code' => 'return User::count();']);
    $result = $tool->handle($request);

    expect($result)->toBeString();
});

test('TinkerTool validates code parameter', function () {
    $tool = app(TinkerTool::class);
    $request = new Request(['code' => '']);
    $result = $tool->handle($request);

    expect($result)->toContain('Error');
    expect($result)->toContain('code');
});

test('TinkerTool respects timeout parameter', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->with('tinker', ['code' => 'sleep(1);', 'timeout' => 10])
        ->andReturn('result');

    $tool = app(TinkerTool::class);
    $request = new Request(['code' => 'sleep(1);', 'timeout' => 10]);
    $result = $tool->handle($request);

    expect($result)->toBe('result');
});

test('TinkerTool caps timeout to 60 seconds', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->with('tinker', ['code' => 'test', 'timeout' => 60])
        ->andReturn('result');

    $tool = app(TinkerTool::class);
    $request = new Request(['code' => 'test', 'timeout' => 120]);
    $result = $tool->handle($request);

    expect($result)->toBe('result');
});

test('TinkerTool has correct schema', function () {
    $tool = app(TinkerTool::class);
    $schema = $tool->schema(new JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('code');
    expect($schema)->toHaveKey('timeout');
});

test('TinkerTool strips PHP opening tags', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->once()
        ->with('tinker', [
            'code' => 'return 2 + 2;',
            'timeout' => 30,
        ])
        ->andReturn('4');

    $tool = app(TinkerTool::class);
    $request = new Request(['code' => '<?php return 2 + 2;']);
    $result = $tool->handle($request);

    expect($result)->toBe('4');
});

test('TinkerTool strips short PHP opening tags', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->once()
        ->with('tinker', [
            'code' => 'echo "hello";',
            'timeout' => 30,
        ])
        ->andReturn('hello');

    $tool = app(TinkerTool::class);
    $request = new Request(['code' => '<? echo "hello";']);
    $result = $tool->handle($request);

    expect($result)->toBe('hello');
});

// Error handling tests

test('DatabaseQueryTool handles MCP client exceptions', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->andThrow(new Exception('Connection failed'));

    $tool = app(DatabaseQueryTool::class);
    $request = new Request(['query' => 'SELECT 1']);
    $result = $tool->handle($request);

    expect($result)->toContain('Database query error');
});

test('SearchDocsTool handles MCP client exceptions', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->andThrow(new Exception('Search failed'));

    $tool = app(SearchDocsTool::class);
    $request = new Request(['queries' => ['test']]);
    $result = $tool->handle($request);

    expect($result)->toContain('Documentation search error');
});

test('TinkerTool handles MCP client exceptions', function () {
    $this->mockMcpClient->shouldReceive('callTool')
        ->andThrow(new Exception('Execution failed'));

    $tool = app(TinkerTool::class);
    $request = new Request(['code' => 'invalid code']);
    $result = $tool->handle($request);

    expect($result)->toContain('Tinker execution error');
});
