## Context

The Laravel AI Assistant is a growing application with AI agent integration (DevBot), MCP tool support, conversation management, and project creation capabilities. Currently, the codebase follows a basic MVC pattern with logic distributed across controllers and models. The `ChatController` is 182 lines with mixed concerns: validation, conversation management, AI agent invocation, error handling, and response formatting.

As we add more features (project templates, GitHub integration, advanced AI tools), this pattern will lead to:

- Fat controllers with 100+ line methods
- Models with business logic that should be reusable
- Duplicated code across different entry points (web, API, CLI)
- Difficult testing due to tightly coupled concerns

Laravel 13 provides excellent support for modern PHP patterns (readonly properties, enums, attributes) that enable cleaner architecture.

## Goals / Non-Goals

**Goals:**

- Establish professional Laravel 13 architecture with Actions, DTOs, Enums, and ViewModels
- Refactor existing controllers to be thin (1-5 lines per method)
- Create reusable, testable business logic in Action classes
- Eliminate magic strings with type-safe Enums
- Standardize data flow: Request → DTO → Action → Resource/ViewModel
- Maintain backward compatibility (no breaking API changes)
- Improve testability with isolated, single-responsibility classes

**Non-Goals:**

- Migrating to service-oriented or microservices architecture
- Implementing CQRS or event sourcing patterns
- Changing database schema or migrations
- Refactoring third-party integrations (MCP, AI providers) unless they directly benefit
- Performance optimization (focus is on code structure, not speed)

## Decisions

### 1. Actions vs Services

**Decision:** Use Actions for single-responsibility use cases, keep Services for complex third-party integrations.

**Rationale:** Actions are purpose-built for one task (e.g., `CreateConversationAction`, `SendMessageAction`), making them highly testable and reusable. Services remain for multi-faceted integrations like `McpClientService` that coordinate multiple external systems.

**Alternatives considered:**

- ❌ Service classes for everything: Leads to "god services" with mixed concerns
- ❌ Command pattern: Overkill for most use cases; Actions are simpler

### 2. DTOs with Readonly Properties

**Decision:** Use PHP 8.3 readonly properties with constructor promotion for DTOs.

**Rationale:** Immutable data objects prevent accidental state mutations. Constructor promotion reduces boilerplate. Named constructors (e.g., `fromRequest()`) provide clean instantiation from different sources.

**Example:**

```php
class MessageData
{
    public function __construct(
        public readonly string $content,
        public readonly ?int $conversationId,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            content: $request->input('message'),
            conversationId: $request->input('conversation_id'),
        );
    }
}
```

### 3. Enums for Status and Types

**Decision:** Use backed enums with metadata methods for all status/type fields.

**Rationale:** Laravel 13's enhanced enum casting allows automatic transformation. Metadata methods (labels, colors, icons) keep UI logic out of controllers.

**Example:**

```php
enum MessageRole: string
{
    case User = 'user';
    case Assistant = 'assistant';

    public function label(): string
    {
        return match($this) {
            self::User => 'User',
            self::Assistant => 'DevBot',
        };
    }
}
```

### 4. ViewModels for Complex View Data

**Decision:** Create ViewModels only when view data preparation exceeds 3-5 lines or involves complex transformations.

**Rationale:** Simple views don't need ViewModels (adds overhead). Complex data aggregation (e.g., chat sidebar with multiple conversations, message counts, unread indicators) benefits from dedicated classes.

### 5. Dependency Injection Pattern

**Decision:** Use Laravel's service container for Action injection in controllers.

**Rationale:** Enables automatic dependency resolution, easier mocking in tests, and follows Laravel conventions. Actions receive dependencies via constructor, data via `execute()` method.

### 6. Gradual Migration Strategy

**Decision:** Refactor incrementally, starting with new features, then migrate existing code.

**Rationale:** Reduces risk of breaking changes. Each refactor is isolated and testable. We can validate the pattern works before committing to full migration.

**Migration order:**

1. Establish folder structure and base classes
2. Refactor `ChatController` (highest impact, most complex)
3. Refactor project creation flow
4. Refactor DevBot agent execution
5. Update tests to use new architecture

## Risks / Trade-offs

**[Risk] Over-engineering for current scale** → Mitigation: Start small, only introduce patterns where they solve real problems. Keep simple views/controllers as-is if they're under 10 lines.

**[Risk] Increased file count** → Mitigation: Each new class has clear purpose and is well-tested. Modern IDEs handle large codebases easily. Benefits outweigh file count concerns.

**[Risk] Learning curve for team** → Mitigation: Document patterns with examples. Create base classes with PHPDoc. Use consistent naming conventions.

**[Trade-off] More boilerplate initially** → Accept this trade-off for long-term maintainability. Laravel's make commands and code generation reduce boilerplate over time.

**[Trade-off] Slight performance overhead from extra classes** → Negligible in practice. PHP's opcache optimizes class loading. Focus on developer productivity and code quality.

## Migration Plan

### Phase 1: Foundation (Week 1)

- Create directory structure (`Actions/`, `DTOs/`, `Enums/`, `ViewModels/`, `Casts/`)
- Implement Enums for existing status/type fields
- Create base Action class with common patterns
- Add DTOs for chat messaging

### Phase 2: Chat Refactor (Week 1-2)

- Create `SendMessageAction` to extract AI logic from `ChatController`
- Create `CreateConversationAction` for conversation creation
- Create `ChatViewModel` for view data preparation
- Refactor `ChatController` to thin methods
- Update tests

### Phase 3: Project Creation Refactor (Week 2-3)

- Extract project creation logic into Actions
- Create DTOs for project configuration
- Update project creation tests

### Phase 4: Cleanup & Documentation (Week 3)

- Remove unused code
- Update AGENTS.md with new architecture patterns
- Run full test suite
- Code review and refinement

### Rollback Strategy

Since this is an internal refactoring with no API changes, rollback is straightforward:

- Each Action/DTO/ViewModel is additive
- Old controller methods remain functional until new ones are verified
- Git branching allows reverting individual phases
- No database migrations means no data rollback needed

## Open Questions

1. **Should we create base Action classes?**
    - Leaning toward yes: `BaseAction` with common validation/error handling
    - Decision pending: Review existing Actions to identify patterns

2. **DTO validation location?**
    - Option A: Validate in DTO constructor (fail fast)
    - Option B: Validate in Form Request, DTO assumes valid data (current Laravel convention)
    - Recommendation: Option B to leverage Laravel's validation pipeline

3. **ViewModel vs Resource for API responses?**
    - ViewModels for Blade views, Resources for API responses
    - JSON:API Resources for external APIs (future consideration)
