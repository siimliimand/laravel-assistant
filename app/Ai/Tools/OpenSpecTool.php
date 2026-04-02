<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class OpenSpecTool implements Tool
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = config('ai.projects.base_path', storage_path('projects'));
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Check OpenSpec workflow status and get instructions for project specification management. Use this tool to verify that proposal, design, specs, and tasks artifacts have been created correctly.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $action = $request['action'] ?? null;

        try {
            return match ($action) {
                'getStatus' => $this->getStatus($request['project'] ?? ''),
                'getInstructions' => $this->getInstructions($request['project'] ?? ''),
                default => 'Error: Invalid action. Available actions: getStatus, getInstructions',
            };
        } catch (\Exception $e) {
            $errorMessage = "OpenSpecTool error: {$e->getMessage()}";
            Log::error('OpenSpecTool: Operation failed', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return $errorMessage;
        }
    }

    /**
     * Get OpenSpec status for a project.
     */
    protected function getStatus(string $project): string
    {
        if (empty($project)) {
            return 'Error: Project name is required.';
        }

        $projectPath = $this->basePath.'/'.$project.'/openspec';

        if (! is_dir($projectPath)) {
            return "OpenSpec directory not found for project '{$project}'. Use the openspec-propose skill to generate initial artifacts.";
        }

        $requiredArtifacts = [
            'proposal.md' => 'Proposal',
            'design.md' => 'Design',
            'specs' => 'Specifications',
            'tasks.md' => 'Tasks',
        ];

        $status = [];
        foreach ($requiredArtifacts as $path => $label) {
            $fullPath = $projectPath.'/'.$path;
            $exists = is_file($fullPath) || is_dir($fullPath);
            $status[$label] = [
                'exists' => $exists,
                'path' => $path,
            ];
        }

        $allComplete = array_reduce($status, fn ($carry, $item) => $carry && $item['exists'], true);

        return json_encode([
            'project' => $project,
            'complete' => $allComplete,
            'artifacts' => $status,
            'message' => $allComplete
                ? 'All OpenSpec artifacts are present.'
                : 'Some artifacts are missing. Use openspec-propose to generate them.',
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Get OpenSpec workflow instructions.
     */
    protected function getInstructions(string $project): string
    {
        if (empty($project)) {
            return 'Error: Project name is required.';
        }

        $instructions = <<<'INSTRUCTIONS'
# OpenSpec Workflow Instructions

## Overview
OpenSpec is a specification-driven development workflow that helps structure projects with clear requirements and implementation plans.

## Required Artifacts

1. **proposal.md** - Why this project exists and what problems it solves
2. **design.md** - Technical design decisions and architecture
3. **specs/** - Detailed specification files with scenarios
4. **tasks.md** - Implementation task checklist

## Workflow Steps

### 1. Generate Initial Artifacts
Use the `openspec-propose` skill to create all required artifacts:
```
/opsx:propose <project-description>
```

### 2. Review and Refine
Review the generated artifacts and make adjustments as needed:
- Ensure proposal clearly states the problem and solution
- Verify design decisions are well-reasoned
- Check that specs cover all requirements
- Confirm tasks are actionable and complete

### 3. Implement Tasks
Use the `openspec-apply` skill to work through tasks:
```
/opsx:apply
```

### 4. Verify Implementation
Use the `openspec-verify` skill to ensure implementation matches specs:
```
/opsx:verify
```

### 5. Archive Completed Work
Once all tasks are complete and verified:
```
/opsx:archive
```

## Best Practices

- Keep specs focused on behavior, not implementation details
- Use scenarios to clarify requirements
- Break tasks into small, testable units
- Update artifacts if requirements change during implementation

## Available Skills

- `openspec-propose` - Generate initial proposal and artifacts
- `openspec-apply` - Implement tasks from the tasks checklist
- `openspec-verify` - Verify implementation matches specifications
- `openspec-archive` - Archive completed change
- `openspec-continue` - Continue working on incomplete artifacts
INSTRUCTIONS;

        return $instructions;
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string('The action to perform. One of: getStatus, getInstructions'),
            'project' => $schema->string('Project slug'),
        ];
    }
}
