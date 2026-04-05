## Context

The application currently supports conversations between users and DevBot (AI agent), but lacks a higher-level organizational structure. Users can create multiple conversations but cannot group them or track work at a project level. The existing architecture follows Laravel best practices with resource controllers, form requests, enum casting, and user-scoped data access.

Current state:
- Users own conversations via `user_id` foreign key
- Conversations have status tracking via `ConversationStatus` enum
- All data is scoped to authenticated users
- Views use Blade components with Tailwind CSS
- Tests use Pest with factories

Constraints:
- Must follow existing Laravel 13 patterns and code conventions
- Must maintain user data isolation (users can only access their own data)
- Must use existing Blade component library for UI consistency
- Must include comprehensive test coverage

## Goals / Non-Goals

**Goals:**
- Enable users to create, view, edit, and delete projects
- Provide status tracking for projects (Draft, Active, Completed, Archived)
- Ensure strict user ownership and data isolation
- Follow existing architectural patterns (resource controllers, form requests, enum casting)
- Provide intuitive UI consistent with existing views
- Comprehensive test coverage (feature and unit tests)

**Non-Goals:**
- Linking conversations to projects (future enhancement)
- Project collaboration/sharing between users
- File attachments or project resources
- Project templates or cloning
- Advanced filtering/search (basic list view only)

## Decisions

### 1. Resource Controller vs. Action Classes
**Decision**: Use resource controller for projects
**Rationale**: Projects require full CRUD with views (not API-only). While conversations use Action classes for API endpoints, projects are user-facing with traditional web forms. Resource controllers are the Laravel standard for this pattern.
**Alternatives considered**:
- Action classes: Better for API endpoints, but overkill for simple web CRUD
- Separate controllers per action: Too fragmented for standard CRUD

### 2. Enum States
**Decision**: Four states - Draft, Active, Completed, Archived
**Rationale**: Matches common project lifecycle. Draft for planning, Active for in-progress, Completed for finished work, Archived for historical reference. Excludes "Deleted" state since soft deletes aren't needed (cascade delete on user deletion is sufficient).
**Alternatives considered**:
- Added "Deleted" state: Unnecessary complexity; hard delete is fine for user-owned data
- Simpler two-state (Active/Archived): Insufficient for workflow tracking

### 3. Database Design
**Decision**: Simple table with `user_id`, `name`, `description`, `status`, timestamps
**Rationale**: Minimal viable structure. Description is nullable to allow quick project creation. Indexed on `user_id` and `created_at` for efficient user-scoped queries sorted by date.
**Alternatives considered**:
- Added `slug` field: Unnecessary; projects accessed by ID, not URL slugs
- Added `due_date` or `priority`: Out of scope for MVP

### 4. Ownership Enforcement
**Decision**: Scope all queries through `auth()->user()->projects()` relationship
**Rationale**: Following the established pattern from conversations. This ensures users can never access another user's projects, even with manipulated IDs. Route model binding will be combined with manual ownership verification.
**Alternatives considered**:
- Policy-based authorization: More complex; scoping through relationship is simpler and equally secure
- Global scopes: Could work but less explicit; relationship scoping is clearer

### 5. Form Request Validation
**Decision**: Separate StoreProjectRequest and UpdateProjectRequest
**Rationale**: Follows Laravel best practices and existing patterns. Keeps validation logic out of controllers. UpdateRequest can have different validation rules if needed (e.g., allowing status changes that StoreRequest doesn't).
**Alternatives considered**:
- Single request class: Less flexible; validation rules often differ between create/update
- Inline validation in controller: Violates separation of concerns

## Risks / Trade-offs

### [Risk] Users accidentally delete projects with important data
**Mitigation**: Add confirmation dialog on delete button. Future enhancement could add soft deletes or project archival instead of deletion.

### [Risk] Project list becomes unwieldy with many projects
**Mitigation**: Current design shows all projects. Future enhancement could add pagination, filtering by status, or search.

### [Trade-off] No project-conversation relationship yet
**Impact**: Projects exist in isolation from conversations initially
**Mitigation**: Database schema designed to allow adding `project_id` to conversations table later as foreign key

### [Trade-off] Simple list view vs. dashboard cards
**Impact**: Less visual appeal but faster to implement
**Mitigation**: Can enhance UI later; focusing on functionality first
