## ADDED Requirements

### Requirement: DTOs SHALL use PHP 8.3 readonly properties with constructor promotion

All Data Transfer Objects SHALL be immutable, using PHP 8.3 readonly properties declared via constructor promotion. DTOs SHALL NOT have setters or mutable state.

#### Scenario: DTO is created with constructor promotion

- **WHEN** creating a new DTO instance
- **THEN** all properties are set via constructor and cannot be modified afterward

#### Scenario: DTO prevents mutation after creation

- **WHEN** code attempts to set a DTO property after instantiation
- **THEN** PHP throws an error due to readonly property restriction

### Requirement: DTOs SHALL provide named constructors for common instantiation patterns

DTOs SHALL include static factory methods (e.g., `fromRequest()`, `fromArray()`, `fromModel()`) to create instances from different data sources with proper type casting.

#### Scenario: DTO created from HTTP request

- **WHEN** calling `MessageData::fromRequest($request)`
- **THEN** the DTO extracts and type-casts values from the request with proper validation

#### Scenario: DTO created from array data

- **WHEN** calling `ProjectData::fromArray($config)`
- **THEN** the DTO maps array keys to typed properties with defaults for missing values

### Requirement: DTOs SHALL NOT contain business logic

DTOs SHALL only hold data and provide simple transformation methods (e.g., `toArray()`). Business logic, validation, and operations SHALL be in Actions or Services.

#### Scenario: DTO provides data access only

- **WHEN** accessing DTO properties
- **THEN** they return the stored values without side effects or business logic execution

### Requirement: DTOs SHALL be used for data transfer between layers

Raw arrays and `$request->all()` SHALL NOT be passed between controllers, actions, and services. DTOs SHALL provide type-safe data transfer with IDE auto-completion.

#### Scenario: Controller passes DTO to Action

- **WHEN** a controller receives an HTTP request
- **THEN** it creates a DTO from the request and passes it to the Action's `execute()` method

#### Scenario: Action receives typed data with auto-completion

- **WHEN** an Action's `execute()` method receives a DTO
- **THEN** the IDE provides auto-completion for all DTO properties and their types
