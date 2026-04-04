<?php

use App\Ai\Tools\DatabaseQueryTool;
use App\Ai\Tools\DatabaseSchemaTool;
use App\Ai\Tools\SearchDocsTool;
use App\Ai\Tools\TinkerTool;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->schema = new JsonSchemaTypeFactory;
});

test('database query tool implements tool interface', function () {
    $tool = app(DatabaseQueryTool::class);

    expect($tool)->toBeInstanceOf(Tool::class);
});

test('database query tool has description', function () {
    $tool = app(DatabaseQueryTool::class);

    expect($tool->description())->toBeString()
        ->and($tool->description())->toContain('read-only')
        ->and($tool->description())->toContain('SELECT');
});

test('database query tool has schema', function () {
    $tool = app(DatabaseQueryTool::class);
    $schema = $tool->schema($this->schema);

    expect($schema)->toBeArray()
        ->and($schema)->toHaveKey('query')
        ->and($schema)->toHaveKey('database');
});

test('database query tool executes select query', function () {
    $tool = app(DatabaseQueryTool::class);
    $request = new Request(['query' => 'SELECT name FROM sqlite_master WHERE type="table"']);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->not->toContain('Error');
})->skip('Requires real MCP server connection');

test('database query tool blocks insert query', function () {
    $tool = app(DatabaseQueryTool::class);
    $request = new Request(['query' => 'INSERT INTO users (name) VALUES ("test")']);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Error')
        ->and($result)->toContain('read-only');
});

test('database query tool blocks delete query', function () {
    $tool = app(DatabaseQueryTool::class);
    $request = new Request(['query' => 'DELETE FROM users']);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Error')
        ->and($result)->toContain('read-only');
});

test('database query tool handles invalid query', function () {
    $tool = app(DatabaseQueryTool::class);
    $request = new Request(['query' => 'SELECT * FROM nonexistent_table']);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('error');
})->skip('Requires real MCP server connection');

test('database schema tool implements tool interface', function () {
    $tool = app(DatabaseSchemaTool::class);

    expect($tool)->toBeInstanceOf(Tool::class);
});

test('database schema tool has description', function () {
    $tool = app(DatabaseSchemaTool::class);

    expect($tool->description())->toBeString()
        ->and($tool->description())->toContain('database schema')
        ->and($tool->description())->toContain('table');
});

test('database schema tool has schema', function () {
    $tool = app(DatabaseSchemaTool::class);
    $schema = $tool->schema($this->schema);

    expect($schema)->toBeArray()
        ->and($schema)->toHaveKey('table')
        ->and($schema)->toHaveKey('database');
});

test('database schema tool lists all tables', function () {
    $tool = app(DatabaseSchemaTool::class);
    $request = new Request([]);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('tables')
        ->and($result)->toContain('count');
})->skip('Requires real MCP server connection');

test('database schema tool returns error for nonexistent table', function () {
    $tool = app(DatabaseSchemaTool::class);
    $request = new Request(['table' => 'nonexistent_table_xyz']);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Error')
        ->and($result)->toContain('does not exist');
})->skip('Requires real MCP server connection');

test('search docs tool implements tool interface', function () {
    $tool = app(SearchDocsTool::class);

    expect($tool)->toBeInstanceOf(Tool::class);
});

test('search docs tool has description', function () {
    $tool = app(SearchDocsTool::class);

    expect($tool->description())->toBeString()
        ->and($tool->description())->toContain('documentation')
        ->and(strtolower($tool->description()))->toContain('search');
});

test('search docs tool has schema', function () {
    $tool = app(SearchDocsTool::class);
    $schema = $tool->schema($this->schema);

    expect($schema)->toBeArray()
        ->and($schema)->toHaveKey('queries')
        ->and($schema)->toHaveKey('packages')
        ->and($schema)->toHaveKey('token_limit');
});

test('search docs tool requires queries parameter', function () {
    $tool = app(SearchDocsTool::class);
    $request = new Request([]);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Error')
        ->and($result)->toContain('queries');
});

test('search docs tool searches documentation', function () {
    $tool = app(SearchDocsTool::class);
    $request = new Request(['queries' => ['eloquent', 'relationships']]);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Laravel Documentation Search');
})->skip('Requires real MCP server connection');

test('tinker tool implements tool interface', function () {
    $tool = app(TinkerTool::class);

    expect($tool)->toBeInstanceOf(Tool::class);
});

test('tinker tool has description', function () {
    $tool = app(TinkerTool::class);

    expect($tool->description())->toBeString()
        ->and($tool->description())->toContain('PHP code')
        ->and($tool->description())->toContain('tinker');
});

test('tinker tool has schema', function () {
    $tool = app(TinkerTool::class);
    $schema = $tool->schema($this->schema);

    expect($schema)->toBeArray()
        ->and($schema)->toHaveKey('code')
        ->and($schema)->toHaveKey('timeout');
});

test('tinker tool requires code parameter', function () {
    $tool = app(TinkerTool::class);
    $request = new Request([]);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Error')
        ->and($result)->toContain('code');
});

test('tinker tool executes simple php code', function () {
    $tool = app(TinkerTool::class);
    $request = new Request(['code' => 'return 2 + 2;']);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('4');
})->skip('Requires real MCP server connection');

test('tinker tool executes laravel helper', function () {
    $tool = app(TinkerTool::class);
    $request = new Request(['code' => 'return config("app.name");']);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Laravel');
})->skip('Requires real MCP server connection');

test('tinker tool handles exceptions', function () {
    $tool = app(TinkerTool::class);
    $request = new Request(['code' => 'throw new \Exception("Test exception");']);

    $result = $tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Test exception');
})->skip('Requires real MCP server connection');
