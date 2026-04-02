<?php

use App\Ai\Tools\FileSystemTool;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->schema = new JsonSchemaTypeFactory;
    $this->tool = new FileSystemTool;
});

test('filesystem tool implements tool interface', function () {
    expect($this->tool)->toBeInstanceOf(Tool::class);
});

test('filesystem tool has description', function () {
    expect($this->tool->description())->toBeString()
        ->and($this->tool->description())->toContain('project files')
        ->and($this->tool->description())->toContain('directory traversal');
});

test('filesystem tool has schema', function () {
    $schema = $this->tool->schema($this->schema);

    expect($schema)->toBeArray()
        ->and($schema)->toHaveKey('action')
        ->and($schema)->toHaveKey('name')
        ->and($schema)->toHaveKey('project')
        ->and($schema)->toHaveKey('path')
        ->and($schema)->toHaveKey('content');
});

test('create project creates directory', function () {
    $projectName = 'test-project-'.uniqid();
    $request = new Request([
        'action' => 'createProject',
        'name' => $projectName,
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('created successfully')
        ->and($result)->toContain($projectName);

    // Cleanup
    $basePath = config('ai.projects.base_path');
    if (is_dir($basePath.'/'.$projectName)) {
        rmdir($basePath.'/'.$projectName);
    }
});

test('create project converts to kebab case', function () {
    $request = new Request([
        'action' => 'createProject',
        'name' => 'My Test Project',
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('my-test-project');

    // Cleanup
    $basePath = config('ai.projects.base_path');
    if (is_dir($basePath.'/my-test-project')) {
        rmdir($basePath.'/my-test-project');
    }
});

test('create project rejects duplicate names', function () {
    $projectName = 'duplicate-test-project';
    $basePath = config('ai.projects.base_path');

    // Create the directory first
    if (! is_dir($basePath.'/'.$projectName)) {
        mkdir($basePath.'/'.$projectName, 0755, true);
    }

    $request = new Request([
        'action' => 'createProject',
        'name' => $projectName,
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('already exists')
        ->and($result)->toContain('Consider using');

    // Cleanup
    if (is_dir($basePath.'/'.$projectName)) {
        rmdir($basePath.'/'.$projectName);
    }
});

test('create project rejects empty name', function () {
    $request = new Request([
        'action' => 'createProject',
        'name' => '',
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Project name is required');
});

test('write file creates file in project', function () {
    $projectName = 'write-test-'.uniqid();
    $basePath = config('ai.projects.base_path');

    // Create project
    mkdir($basePath.'/'.$projectName, 0755, true);

    $request = new Request([
        'action' => 'writeFile',
        'project' => $projectName,
        'path' => 'test.txt',
        'content' => 'Hello, World!',
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('written successfully');

    // Verify file exists
    expect(file_exists($basePath.'/'.$projectName.'/test.txt'))->toBeTrue();
    expect(file_get_contents($basePath.'/'.$projectName.'/test.txt'))->toBe('Hello, World!');

    // Cleanup
    unlink($basePath.'/'.$projectName.'/test.txt');
    rmdir($basePath.'/'.$projectName);
});

test('write file creates nested directories', function () {
    $projectName = 'nested-test-'.uniqid();
    $basePath = config('ai.projects.base_path');

    // Create project
    mkdir($basePath.'/'.$projectName, 0755, true);

    $request = new Request([
        'action' => 'writeFile',
        'project' => $projectName,
        'path' => 'subdir/nested/file.txt',
        'content' => 'Nested content',
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('written successfully');

    // Verify file exists
    expect(file_exists($basePath.'/'.$projectName.'/subdir/nested/file.txt'))->toBeTrue();

    // Cleanup
    unlink($basePath.'/'.$projectName.'/subdir/nested/file.txt');
    rmdir($basePath.'/'.$projectName.'/subdir/nested');
    rmdir($basePath.'/'.$projectName.'/subdir');
    rmdir($basePath.'/'.$projectName);
});

test('write file rejects directory traversal', function () {
    $request = new Request([
        'action' => 'writeFile',
        'project' => 'test',
        'path' => '../etc/passwd',
        'content' => 'malicious',
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Error')
        ->and($result)->toContain('Directory traversal');
});

test('write file rejects absolute paths', function () {
    $request = new Request([
        'action' => 'writeFile',
        'project' => 'test',
        'path' => '/etc/passwd',
        'content' => 'malicious',
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Error')
        ->and($result)->toContain('Directory traversal');
});

test('read file returns file content', function () {
    $projectName = 'read-test-'.uniqid();
    $basePath = config('ai.projects.base_path');

    // Create project and file
    mkdir($basePath.'/'.$projectName, 0755, true);
    file_put_contents($basePath.'/'.$projectName.'/test.txt', 'Test content');

    $request = new Request([
        'action' => 'readFile',
        'project' => $projectName,
        'path' => 'test.txt',
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBe('Test content');

    // Cleanup
    unlink($basePath.'/'.$projectName.'/test.txt');
    rmdir($basePath.'/'.$projectName);
});

test('read file returns error for nonexistent file', function () {
    $projectName = 'read-test-'.uniqid();
    $basePath = config('ai.projects.base_path');

    // Create project only
    mkdir($basePath.'/'.$projectName, 0755, true);

    $request = new Request([
        'action' => 'readFile',
        'project' => $projectName,
        'path' => 'nonexistent.txt',
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Error')
        ->and($result)->toContain('not found');

    // Cleanup
    rmdir($basePath.'/'.$projectName);
});

test('read file rejects directory traversal', function () {
    $request = new Request([
        'action' => 'readFile',
        'project' => 'test',
        'path' => '../../.env',
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Error')
        ->and($result)->toContain('Directory traversal');
});

test('list files returns project files', function () {
    $projectName = 'list-test-'.uniqid();
    $basePath = config('ai.projects.base_path');

    // Create project with files
    mkdir($basePath.'/'.$projectName, 0755, true);
    file_put_contents($basePath.'/'.$projectName.'/file1.txt', 'content1');
    file_put_contents($basePath.'/'.$projectName.'/file2.txt', 'content2');

    $request = new Request([
        'action' => 'listFiles',
        'project' => $projectName,
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('file1.txt')
        ->and($result)->toContain('file2.txt');

    // Cleanup
    unlink($basePath.'/'.$projectName.'/file1.txt');
    unlink($basePath.'/'.$projectName.'/file2.txt');
    rmdir($basePath.'/'.$projectName);
});

test('list files returns error for nonexistent project', function () {
    $request = new Request([
        'action' => 'listFiles',
        'project' => 'nonexistent-project',
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Error')
        ->and($result)->toContain('does not exist');
});

test('project exists returns true for existing project', function () {
    $projectName = 'exists-test-'.uniqid();
    $basePath = config('ai.projects.base_path');

    // Create project
    mkdir($basePath.'/'.$projectName, 0755, true);

    $request = new Request([
        'action' => 'projectExists',
        'project' => $projectName,
    ]);

    $result = $this->tool->handle($request);
    $data = json_decode($result, true);

    expect($data)->toBeArray()
        ->and($data['exists'])->toBeTrue()
        ->and($data['project'])->toBe($projectName);

    // Cleanup
    rmdir($basePath.'/'.$projectName);
});

test('project exists returns false for nonexistent project', function () {
    $request = new Request([
        'action' => 'projectExists',
        'project' => 'nonexistent-project',
    ]);

    $result = $this->tool->handle($request);
    $data = json_decode($result, true);

    expect($data)->toBeArray()
        ->and($data['exists'])->toBeFalse();
});

test('invalid action returns error', function () {
    $request = new Request([
        'action' => 'invalidAction',
    ]);

    $result = $this->tool->handle($request);

    expect($result)->toBeString()
        ->and($result)->toContain('Error')
        ->and($result)->toContain('Invalid action');
});
