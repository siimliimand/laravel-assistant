## ADDED Requirements

### Requirement: ViewModels SHALL encapsulate complex view data preparation

ViewModels SHALL be used when view data preparation exceeds 3-5 lines or involves complex transformations, aggregations, or multiple model queries. ViewModels SHALL be located in `app/ViewModels/` namespace.

#### Scenario: ViewModel aggregates data from multiple sources

- **WHEN** a chat view needs conversations list, current messages, and metadata
- **THEN** a `ChatViewModel` aggregates all data in one place instead of the controller

#### Scenario: ViewModel transforms data for presentation

- **WHEN** messages need formatting (timestamps, markdown, role labels)
- **THEN** the ViewModel handles transformations, keeping the controller thin

### Requirement: ViewModels SHALL be instantiated with required data only

ViewModels SHALL receive only the data they need via constructor injection. They SHALL NOT query the database or access request data directly.

#### Scenario: ViewModel receives pre-fetched data

- **WHEN** creating a `ChatViewModel`
- **THEN** it receives Conversation, messages collection, and sidebar data as constructor parameters

#### Scenario: ViewModel does not access database

- **WHEN** a ViewModel method is called
- **THEN** it only transforms already-loaded data, never executes queries

### Requirement: ViewModels SHALL provide public methods for view consumption

ViewModels SHALL expose data through well-named public methods or properties that Blade views can consume directly. Method names SHALL be descriptive and intent-revealing.

#### Scenario: View accesses formatted messages

- **WHEN** a Blade template calls `$viewModel->getFormattedMessages()`
- **THEN** it returns messages formatted for display with timestamps, roles, and content

#### Scenario: View accesses sidebar data

- **WHEN** a Blade template calls `$viewModel->getSidebarConversations()`
- **THEN** it returns conversations formatted for sidebar display with metadata

### Requirement: Controllers SHALL delegate to ViewModels for complex views

When a controller method requires more than passing a single model to a view, it SHALL use a ViewModel to prepare the data. The controller SHALL be limited to 1-5 lines.

#### Scenario: Controller uses ViewModel for chat display

- **WHEN** displaying the chat interface
- **THEN** the controller creates a ViewModel and passes it to the view in 1-2 lines
