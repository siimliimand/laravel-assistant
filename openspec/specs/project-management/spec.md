## ADDED Requirements

### Requirement: Project Model and Schema

The system SHALL provide a Project model backed by a database table with user ownership.

#### Scenario: Project table structure

- **WHEN** the projects table is queried
- **THEN** it SHALL contain columns: id, user_id, name, description, status, created_at, updated_at
- **AND** user_id SHALL be a foreign key referencing users.id with cascade on delete
- **AND** the table SHALL have an index on user_id and created_at

#### Scenario: Project model casting

- **WHEN** a Project model is instantiated
- **THEN** the status attribute SHALL be cast to ProjectStatus enum
- **AND** the name, description, and status attributes SHALL be mass assignable

### Requirement: Project Status Enum

The system SHALL provide a ProjectStatus enum with Draft, Active, Completed, and Archived states.

#### Scenario: Enum value access

- **WHEN** ProjectStatus enum cases are accessed
- **THEN** Draft SHALL have value 'draft'
- **AND** Active SHALL have value 'active'
- **AND** Completed SHALL have value 'completed'
- **AND** Archived SHALL have value 'archived'

#### Scenario: Enum metadata methods

- **WHEN** label() is called on a ProjectStatus case
- **THEN** it SHALL return a human-readable label (e.g., 'Draft', 'Active')
- **AND** WHEN color() is called, it SHALL return a Tailwind CSS color name
- **AND** WHEN icon() is called, it SHALL return an icon identifier

#### Scenario: Enum helper methods

- **WHEN** isDraft() is called on ProjectStatus::Draft
- **THEN** it SHALL return true
- **AND** isDraft() called on other cases SHALL return false
- **AND** isActive(), isCompleted(), isArchived() SHALL follow the same pattern

### Requirement: Project CRUD Operations

The system SHALL allow authenticated users to create, read, update, and delete their own projects.

#### Scenario: Create project

- **WHEN** an authenticated user submits the project creation form with valid data
- **THEN** the system SHALL create a new Project owned by that user
- **AND** the system SHALL redirect to the project show page
- **AND** the system SHALL display a success message

#### Scenario: View project list

- **WHEN** an authenticated user visits the projects index page
- **THEN** the system SHALL display all projects owned by that user
- **AND** the projects SHALL be ordered by created_at descending
- **AND** each project SHALL show name, status badge, and description preview

#### Scenario: View single project

- **WHEN** an authenticated user visits a project show page they own
- **THEN** the system SHALL display the project name, status, and description
- **AND** the system SHALL provide Edit and Delete action buttons

#### Scenario: Update project

- **WHEN** an authenticated user submits the project edit form with valid data
- **THEN** the system SHALL update the project with the new data
- **AND** the system SHALL redirect to the project show page
- **AND** the system SHALL display a success message

#### Scenario: Delete project

- **WHEN** an authenticated user confirms deletion of their project
- **THEN** the system SHALL permanently delete the project from the database
- **AND** the system SHALL redirect to the projects index page
- **AND** the system SHALL display a success message

### Requirement: Project Form Validation

The system SHALL validate all project creation and update requests using Form Request classes.

#### Scenario: Store project validation

- **WHEN** a user submits the create project form
- **THEN** the name field SHALL be required, string, and max 255 characters
- **AND** the description field SHALL be nullable, string, and max 5000 characters
- **AND** the status field SHALL be required and a valid ProjectStatus enum value
- **AND** validation failures SHALL redirect back with error messages

#### Scenario: Update project validation

- **WHEN** a user submits the edit project form
- **THEN** the validation rules SHALL match StoreProjectRequest requirements
- **AND** the system SHALL allow updating all fields including status

### Requirement: Project Ownership Enforcement

All project operations SHALL be strictly scoped to the authenticated user.

#### Scenario: List only user's projects

- **WHEN** an authenticated user requests their project list
- **THEN** the system SHALL return only projects where user_id equals the authenticated user's ID
- **AND** the system SHALL NOT return projects belonging to other users
- **AND** the query SHALL use auth()->user()->projects()

#### Scenario: Access project with ownership verification

- **WHEN** an authenticated user requests a specific project by ID
- **THEN** the system SHALL verify the project belongs to the user
- **AND** if the project belongs to the user, it SHALL be displayed
- **AND** if the project belongs to another user, the system SHALL return a 403 Forbidden error

#### Scenario: Update project ownership check

- **WHEN** an authenticated user attempts to update a project
- **THEN** the system SHALL verify the project belongs to the user
- **AND** if ownership is confirmed, the project SHALL be updated
- **AND** if the project belongs to another user, the system SHALL return a 403 Forbidden error

#### Scenario: Delete project ownership check

- **WHEN** an authenticated user attempts to delete a project
- **THEN** the system SHALL verify the project belongs to the user
- **AND** if ownership is confirmed, the project SHALL be deleted
- **AND** if the project belongs to another user, the system SHALL return a 403 Forbidden error

### Requirement: Project User Relationship

The User model SHALL have a one-to-many relationship with projects.

#### Scenario: Access user's projects

- **WHEN** calling `$user->projects()` on a User model
- **THEN** the system SHALL return all projects owned by that user
- **AND** the projects SHALL be queryable with Eloquent relationship methods
- **AND** the relationship SHALL support eager loading

#### Scenario: Access project's owner

- **WHEN** calling `$project->user()` on a Project model
- **THEN** the system SHALL return the User who owns the project
- **AND** the relationship SHALL be accessible as `$project->user`

### Requirement: Project Routes and Navigation

The system SHALL provide authenticated routes for project management and navigation access.

#### Scenario: Project resource routes

- **WHEN** the application boots
- **THEN** the system SHALL register resource routes for projects under auth middleware
- **AND** routes SHALL include: index, create, store, show, edit, update, destroy
- **AND** all routes SHALL require authentication

#### Scenario: Navigation menu update

- **WHEN** an authenticated user views the navigation menu
- **THEN** the system SHALL display a "Projects" link
- **AND** the link SHALL highlight when on any projects.* route
- **AND** the link SHALL navigate to the projects index page

### Requirement: Project Factory

The system SHALL provide a ProjectFactory for generating test data.

#### Scenario: Factory definition

- **WHEN** ProjectFactory::new()->create() is called
- **THEN** it SHALL create a Project with a valid user_id from UserFactory
- **AND** the name SHALL be a fake sentence
- **AND** the description SHALL be a fake paragraph
- **AND** the status SHALL be a random ProjectStatus enum value

#### Scenario: Factory customization

- **WHEN** a test calls ProjectFactory with custom attributes
- **THEN** the factory SHALL override default values with provided attributes
- **AND** the project SHALL be created with the custom values
