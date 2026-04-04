## Why

As the Laravel AI Assistant project grows beyond its initial scope, the current MVC pattern is showing signs of strain. Business logic is scattered across controllers and models, making the codebase harder to test, maintain, and extend. Professional Laravel 13 architecture emphasizes domain-specific classes with single responsibilities, which will improve code quality, testability, and developer experience as we add more features like AI agents, MCP tools, and project management capabilities.

## What Changes

- **Introduce Actions layer** (`app/Actions/`) for single-responsibility business logic, replacing fat controller methods
- **Add Data Transfer Objects** (`app/DTOs/`) to replace raw array passing with strongly-typed, readonly PHP 8.3 objects
- **Create Enums** (`app/Enums/`) for status values, roles, and types to eliminate magic strings
- **Implement ViewModels** (`app/ViewModels/`) to extract view data preparation logic from controllers
- **Refactor existing controllers** to be thin (1-5 lines per method) by delegating to Actions
- **Establish standardized request flow**: Request → DTO → Action → Resource/ViewModel
- **Add PHP 8.3 attributes** for model observers and casts to reduce boilerplate in service providers

## Capabilities

### New Capabilities

- `action-classes`: Single-responsibility action classes for business logic orchestration
- `data-transfer-objects`: Strongly-typed DTOs using PHP 8.3 readonly properties
- `enum-system`: Native PHP enums for statuses, types, and roles with metadata support
- `view-models`: View-specific data preparation classes to keep controllers thin
- `architectural-standards`: Code structure conventions and folder organization standards

### Modified Capabilities

- `devbot-agent`: Agent execution encapsulated in SendMessageAction (no refactoring needed - already compliant)
- `chat-interface`: Update chat controller to use thin controller pattern with Actions
- `project-creation`: Assessed and confirmed compliant - AI-driven workflow via tools (no refactoring needed)

## Impact

- **Affected Code**: ChatController (refactored), models with business logic (updated to use enums)
- **New Directories**: `app/Actions/`, `app/DTOs/`, `app/Enums/`, `app/ViewModels/`, `app/Casts/`
- **Refactored Classes**: `ChatController` (reduced from 182 lines to 104 lines with thin methods)
- **Assessed Classes**: DevBot agent (confirmed compliant), AI Tools (confirmed compliant)
- **Dependencies**: PHP 8.3+ features (readonly properties, constructor promotion), Laravel 13 enum casting
- **Tests**: All tests updated and passing; new tests for Actions, DTOs, ViewModels, Enums
- **Breaking Changes**: None for external APIs; internal refactoring only
