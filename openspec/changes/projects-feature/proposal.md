## Why

Authenticated users need a way to organize and manage their work through projects. Currently, users can create conversations with DevBot, but there's no higher-level organizational structure to group related conversations and work together. Adding a Projects feature allows users to create, view, and manage their own projects with status tracking, providing better organization and workflow management.

## What Changes

- Add ProjectStatus enum with Draft, Active, Completed, and Archived states
- Create projects database table with user ownership, name, description, and status
- Implement Project model with user relationship and enum casting
- Create ProjectController with full CRUD operations (resource controller)
- Build project management views (index, create, show, edit) using existing Blade components
- Update navigation to include Projects link
- Add comprehensive test coverage for all project operations
- Enforce user scoping: users can only access their own projects

## Capabilities

### New Capabilities

- `project-management`: Full CRUD operations for user-owned projects including creation, viewing, editing, deletion, and status tracking. Covers Project model, controller, routes, views, and ownership enforcement.

### Modified Capabilities

- `user-ownership`: Extended to include project ownership in addition to conversation ownership. Users now own both conversations and projects.

## Impact

- **Database**: New `projects` table with foreign key to `users` table
- **Models**: New Project model, updates to User model (adds `projects()` relationship)
- **Controllers**: New ProjectController with resource routes
- **Views**: New project views (index, create, show, edit)
- **Routes**: New resource routes under auth middleware group
- **Navigation**: Updated navigation menu to include Projects link
- **Tests**: New feature and unit tests for project functionality
- **Enums**: New ProjectStatus enum
