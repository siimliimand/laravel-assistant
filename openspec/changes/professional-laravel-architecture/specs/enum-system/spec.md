## ADDED Requirements

### Requirement: Enums SHALL replace magic strings for status, type, and role fields

All status, type, role, and categorical values SHALL be represented as native PHP backed enums instead of string literals. Enums SHALL be located in `app/Enums/` namespace.

#### Scenario: Enum defines message roles

- **WHEN** representing message roles (user, assistant, system)
- **THEN** a `MessageRole` enum with string backing is used instead of magic strings

#### Scenario: Enum prevents invalid values

- **WHEN** code attempts to use an invalid enum value
- **THEN** PHP throws a TypeError or ValueError at runtime

### Requirement: Enums SHALL provide metadata methods for UI presentation

Enums SHALL include methods for labels, colors, icons, and other UI-related metadata to keep presentation logic out of controllers and views.

#### Scenario: Enum provides human-readable label

- **WHEN** calling `$role->label()` on a `MessageRole` enum
- **THEN** it returns a human-readable string (e.g., "User" or "DevBot Assistant")

#### Scenario: Enum provides color for UI display

- **WHEN** calling `$status->color()` on a status enum
- **THEN** it returns a Tailwind CSS color class (e.g., "bg-green-500", "bg-yellow-500")

### Requirement: Enums SHALL be castable to/from database values

Enums SHALL use Laravel's native enum casting in Eloquent models with the `protected $casts` property. Database columns SHALL store the enum's backing value.

#### Scenario: Model casts database string to enum

- **WHEN** retrieving a model with an enum cast from the database
- **THEN** the string value is automatically converted to the corresponding enum case

#### Scenario: Model casts enum to database string

- **WHEN** saving a model with an enum property
- **THEN** the enum's backing value is stored in the database column

### Requirement: Enums SHALL be used in validation rules

Form Request validation SHALL use `Rule::enum()` to ensure only valid enum values are accepted from user input.

#### Scenario: Validation rejects invalid enum value

- **WHEN** a request contains an invalid role value
- **THEN** validation fails with a 422 error listing valid enum values

#### Scenario: Validation accepts valid enum value

- **WHEN** a request contains a valid enum backing value
- **THEN** validation passes and the value can be safely cast to the enum
