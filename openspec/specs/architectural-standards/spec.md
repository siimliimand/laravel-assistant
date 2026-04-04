## ADDED Requirements

### Requirement: Controllers SHALL be thin with 1-5 lines per method

Controller methods SHALL only handle HTTP-specific concerns: receiving requests, delegating to Actions/ViewModels, and returning responses. Business logic SHALL NOT exist in controllers.

#### Scenario: Controller delegates to Action for business logic

- **WHEN** a POST request creates a new resource
- **THEN** the controller validates, creates a DTO, calls an Action, and returns the response in 1-5 lines

#### Scenario: Controller delegates to ViewModel for view data

- **WHEN** a GET request displays a complex view
- **THEN** the controller creates a ViewModel and passes it to the view in 1-2 lines

### Requirement: app/ directory SHALL follow professional folder structure

The application SHALL organize code into domain-specific directories: `Actions/`, `DTOs/`, `Enums/`, `ViewModels/`, `Casts/`, alongside existing `Models/`, `Services/`, `Http/`.

#### Scenario: New Action class location

- **WHEN** creating a new business operation
- **THEN** it is placed in `app/Actions/` with naming convention `{Verb}{Noun}Action.php`

#### Scenario: New DTO class location

- **WHEN** creating a data transfer object
- **THEN** it is placed in `app/DTOs/` with naming convention `{Noun}Data.php`

### Requirement: Request flow SHALL follow standardized pipeline

All requests SHALL follow the pattern: HTTP Request → Form Request (validation) → DTO → Action → Resource/ViewModel → Response. Deviations SHALL be documented and justified.

#### Scenario: POST request follows standard pipeline

- **WHEN** a user submits a form to create a resource
- **THEN** the request flows through validation, DTO creation, Action execution, and resource response

#### Scenario: GET request follows standard pipeline

- **WHEN** a user requests a page with complex data
- **THEN** the request flows through data fetching, ViewModel preparation, and view rendering

### Requirement: Models SHALL contain only database interaction and relationships

Eloquent models SHALL be limited to: attribute definitions, relationships, casts, scopes, and simple accessors/mutators. Business logic SHALL be extracted to Actions or Services.

#### Scenario: Model defines relationships only

- **WHEN** a Conversation model needs related messages
- **THEN** it defines a `messages()` relationship method, not business logic for message processing

#### Scenario: Model uses enum casts

- **WHEN** a model has a status or role field
- **THEN** it uses PHP enum casting in the `$casts` property

### Requirement: Code SHALL use PHP 8.3 features consistently

All new code SHALL use PHP 8.3 features: readonly properties, constructor promotion, typed properties, and union types. Legacy code SHALL be updated incrementally during refactoring.

#### Scenario: DTO uses readonly properties

- **WHEN** creating a new DTO class
- **THEN** all properties are declared as readonly with constructor promotion

#### Scenario: Action uses constructor property promotion

- **WHEN** creating a new Action class
- **THEN** dependencies are injected via constructor property promotion syntax
