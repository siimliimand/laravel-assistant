## Context

The application is a Laravel 13 AI chat assistant that currently operates without authentication. All users share the same conversation pool, and there's no user isolation. The database schema already includes a `user_id` column on the conversations table (nullable), and the Conversation model has `user_id` in its fillable array, but no authentication system exists to leverage this.

The User model is already configured as an Authenticatable with proper password hashing, making it ready for Breeze integration.

## Goals / Non-Goals

**Goals:**

- Add complete email/password authentication using Laravel Breeze
- Protect all chat routes with authentication middleware
- Establish user ownership of conversations with proper relationships
- Scope all conversation operations to authenticated users
- Maintain existing functionality while adding auth layer
- Ensure all tests pass with authenticated context

**Non-Goals:**

- OAuth/social login providers (can be added later)
- Role-based access control or permissions
- Email verification enforcement (available but not required initially)
- Multi-tenant architecture beyond single-user isolation
- API authentication (sanctum/passport) - web session auth only

## Decisions

### 1. Use Laravel Breeze with Blade Stack

**Decision:** Install Laravel Breeze with the Blade stack (not Inertia or Livewire).

**Rationale:**

- Minimal overhead and simplest integration path
- Blade templates align with existing view structure
- Alpine.js provides lightweight interactivity without build complexity
- Controllers and routes are scaffolded as standard Laravel code (easy to customize)
- Matches project's existing Tailwind CSS setup

**Alternatives considered:**

- Laravel Jetstream: Too heavy, includes Teams/API features we don't need
- Manual implementation: Unnecessary reinvention, Breeze is officially supported
- Livewire stack: Adds complexity without clear benefit for this use case

### 2. Make user_id Required on Conversations

**Decision:** Change `user_id` from nullable to required with a foreign key constraint.

**Rationale:**

- Ensures data integrity - every conversation must belong to a user
- Prevents orphaned conversations
- Simplifies query scoping (no need to handle null user_id cases)

**Migration strategy:**

- Create new migration to add foreign key constraint
- Update existing nullable column to not nullable with default value for existing data
- Add cascade on delete to clean up conversations when user is deleted

### 3. Scope All Queries to Authenticated User

**Decision:** All conversation-related Actions must filter by `auth()->id()`.

**Rationale:**

- Prevents users from accessing other users' conversations
- Simple, explicit approach using Laravel's auth helper
- Consistent with Laravel best practices for multi-tenant data

**Implementation:**

- `ListConversationsAction`: `Conversation::where('user_id', auth()->id())`
- `GetConversationAction`: Validate conversation belongs to user
- `CreateConversationAction`: Set `user_id` to `auth()->id()`
- `SendMessageAction`: Validate conversation ownership before adding messages

### 4. Redirect Dashboard to Chat

**Decision:** After authentication, redirect users to `/chat` instead of Breeze's default dashboard.

**Rationale:**

- Chat is the primary application feature
- Avoids maintaining an unnecessary dashboard view
- Better user experience - users land directly in the app

**Implementation:**

- Update `RouteServiceProvider` or Breeze's authenticated redirect in `.env`: `HOME=/chat`

### 5. Update Existing Factories and Tests

**Decision:** All Conversation factories must create associated users, and tests must use `actingAs()`.

**Rationale:**

- Maintains test integrity with new auth requirements
- Follows Laravel testing best practices
- Ensures factories produce valid data with required relationships

## Risks / Trade-offs

### [Risk] Existing conversation data lacks user_id

**Mitigation:** Migration will handle existing data by either:

- Assigning orphaned conversations to a system user
- Deleting orphaned conversations (acceptable for development environment)
- Current app is in development, so data loss is acceptable

### [Risk] Breaking change for any existing integrations

**Mitigation:**

- All chat endpoints now require auth - documented as BREAKING change
- Tests will be updated to use `actingAs()` before migration
- Clear error messages redirect to login

### [Risk] Session management complexity

**Mitigation:**

- Laravel's default session handling is robust
- Breeze includes "remember me" functionality
- Session timeout configured via Laravel defaults (configurable in .env)

### [Trade-off] Web session auth only (no API tokens)

**Acceptance:**

- Current app is web-only (Blade templates)
- API authentication can be added later if needed via Sanctum
- Simpler initial implementation

### [Trade-off] No email verification enforcement initially

**Acceptance:**

- Users can register and use app immediately
- Email verification available but not required
- Can be enabled later by implementing `MustVerifyEmail` interface

## Migration Plan

### Deployment Steps:

1. Install Laravel Breeze via Composer
2. Run `php artisan breeze:install blade --no-interaction`
3. Install npm dependencies and rebuild assets
4. Create migration to add foreign key constraint on `user_id`
5. Update models with relationships
6. Update Actions to scope by authenticated user
7. Wrap chat routes in `auth` middleware
8. Update factories and tests
9. Run migrations: `php artisan migrate`
10. Run test suite to verify functionality

### Rollback Strategy:

1. Remove `auth` middleware from routes
2. Rollback migration: `php artisan migrate:rollback`
3. Remove Breeze package: `composer remove laravel/breeze`
4. Remove auth-related views and controllers
5. Rebuild assets

**Note:** Rollback will leave auth scaffolding in place but inactive. Full cleanup requires manual removal of Breeze files.

## Open Questions

1. **Should we create a default "system" user for existing orphaned conversations?**
    - Decision: Not needed for development environment, but should be considered before production
2. **Should email verification be required?**
    - Decision: No, keep it optional initially. Can enable later by adding `MustVerifyEmail` interface to User model.

3. **Should we keep the Breeze dashboard or remove it?**
    - Decision: Redirect to `/chat` instead, but keep dashboard route available for future customization.
