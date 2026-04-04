# Architectural Standards Specification

<cite>
**Referenced Files in This Document**
- [BaseAction.php](file://app/Actions/BaseAction.php)
- [ApiResponseData.php](file://app/DTOs/ApiResponseData.php)
- [ConversationData.php](file://app/DTOs/ConversationData.php)
- [MessageData.php](file://app/DTOs/MessageData.php)
- [ConversationStatus.php](file://app/Enums/ConversationStatus.php)
- [MessageRole.php](file://app/Enums/MessageRole.php)
- [ChatViewModel.php](file://app/ViewModels/ChatViewModel.php)
- [ChatController.php](file://app/Http/Controllers/ChatController.php)
- [Conversation.php](file://app/Models/Conversation.php)
- [Message.php](file://app/Models/Message.php)
- [DevBot.php](file://app/Ai/Agents/DevBot.php)
- [McpClientService.php](file://app/Services/McpClientService.php)
- [architecture.md](file://.agents/skills/laravel-best-practices/rules/architecture.md)
- [SKILL.md](file://.agents/skills/laravel-best-practices/SKILL.md)
- [app.php](file://bootstrap/app.php)
- [composer.json](file://composer.json)
</cite>

## Table of Contents
1. [Introduction](#introduction)
2. [Project Structure](#project-structure)
3. [Core Components](#core-components)
4. [Architecture Overview](#architecture-overview)
5. [Detailed Component Analysis](#detailed-component-analysis)
6. [Dependency Analysis](#dependency-analysis)
7. [Performance Considerations](#performance-considerations)
8. [Troubleshooting Guide](#troubleshooting-guide)
9. [Conclusion](#conclusion)

## Introduction
This document defines the architectural standards and design principles for the Laravel Assistant application. It establishes consistent patterns for business logic encapsulation, data transfer, strong typing, presentation layer separation, AI agent integration, and MCP tool connectivity. The standards are derived from the codebase's implementation and the Laravel Best Practices skill, ensuring maintainability, testability, and scalability.

## Project Structure
The application follows Laravel conventions with clear separation of concerns:
- Actions encapsulate business operations
- DTOs standardize data transfer
- Enums enforce strong typing for domain values
- ViewModels handle presentation logic
- Controllers orchestrate requests and delegate to Actions
- Models define domain entities with casts and relationships
- AI agents integrate with Laravel AI and MCP tooling
- Services manage external integrations (e.g., MCP client)

```mermaid
graph TB
subgraph "Presentation Layer"
Controller["ChatController"]
ViewModel["ChatViewModel"]
end
subgraph "Business Logic"
ActionBase["BaseAction"]
CreateConv["CreateConversationAction"]
GetConv["GetConversationAction"]
ListConv["ListConversationsAction"]
SendMsg["SendMessageAction"]
end
subgraph "Data Layer"
ConvModel["Conversation Model"]
MsgModel["Message Model"]
ConvEnum["ConversationStatus Enum"]
RoleEnum["MessageRole Enum"]
ConvDTO["ConversationData DTO"]
MsgDTO["MessageData DTO"]
ApiResp["ApiResponseData DTO"]
end
subgraph "AI Integration"
DevBot["DevBot Agent"]
McpSvc["McpClientService"]
end
Controller --> ActionBase
Controller --> ViewModel
ActionBase --> ConvDTO
ActionBase --> MsgDTO
ViewModel --> ConvModel
ViewModel --> MsgModel
ConvModel --> ConvEnum
MsgModel --> RoleEnum
DevBot --> McpSvc
DevBot --> ConvModel
```

**Diagram sources**
- [ChatController.php:19-154](file://app/Http/Controllers/ChatController.php#L19-L154)
- [ChatViewModel.php:29-120](file://app/ViewModels/ChatViewModel.php#L29-L120)
- [BaseAction.php:28-58](file://app/Actions/BaseAction.php#L28-L58)
- [Conversation.php:9-51](file://app/Models/Conversation.php#L9-L51)
- [Message.php:10-45](file://app/Models/Message.php#L10-L45)
- [ConversationStatus.php:23-89](file://app/Enums/ConversationStatus.php#L23-L89)
- [MessageRole.php:23-77](file://app/Enums/MessageRole.php#L23-L77)
- [ConversationData.php:29-58](file://app/DTOs/ConversationData.php#L29-L58)
- [MessageData.php:29-47](file://app/DTOs/MessageData.php#L29-L47)
- [ApiResponseData.php:31-90](file://app/DTOs/ApiResponseData.php#L31-L90)
- [DevBot.php:30-137](file://app/Ai/Agents/DevBot.php#L30-L137)
- [McpClientService.php:20-279](file://app/Services/McpClientService.php#L20-L279)

**Section sources**
- [ChatController.php:19-154](file://app/Http/Controllers/ChatController.php#L19-L154)
- [ChatViewModel.php:29-120](file://app/ViewModels/ChatViewModel.php#L29-L120)
- [BaseAction.php:28-58](file://app/Actions/BaseAction.php#L28-L58)
- [Conversation.php:9-51](file://app/Models/Conversation.php#L9-L51)
- [Message.php:10-45](file://app/Models/Message.php#L10-L45)
- [DevBot.php:30-137](file://app/Ai/Agents/DevBot.php#L30-L137)
- [McpClientService.php:20-279](file://app/Services/McpClientService.php#L20-L279)

## Core Components
This section outlines the foundational architectural components and their responsibilities:

- BaseAction: Provides consistent error handling and execution wrapping for all business operations
- DTOs: Immutable data carriers for API responses, conversation creation, and message sending
- Enums: Strongly-typed domain values for conversation status and message roles
- ViewModels: Presentation logic for chat interface data formatting
- Controllers: Thin orchestrators delegating to Actions and returning standardized responses
- Models: Domain entities with attribute casting and relationship definitions
- AI Agent: DevBot integrates with Laravel AI and MCP tool ecosystem
- MCP Client Service: Manages connection lifecycle and tool invocation with auto-reconnect

**Section sources**
- [BaseAction.php:28-58](file://app/Actions/BaseAction.php#L28-L58)
- [ApiResponseData.php:31-90](file://app/DTOs/ApiResponseData.php#L31-L90)
- [ConversationData.php:29-58](file://app/DTOs/ConversationData.php#L29-L58)
- [MessageData.php:29-47](file://app/DTOs/MessageData.php#L29-L47)
- [ConversationStatus.php:23-89](file://app/Enums/ConversationStatus.php#L23-L89)
- [MessageRole.php:23-77](file://app/Enums/MessageRole.php#L23-L77)
- [ChatViewModel.php:29-120](file://app/ViewModels/ChatViewModel.php#L29-L120)
- [ChatController.php:19-154](file://app/Http/Controllers/ChatController.php#L19-L154)
- [Conversation.php:9-51](file://app/Models/Conversation.php#L9-L51)
- [Message.php:10-45](file://app/Models/Message.php#L10-L45)
- [DevBot.php:30-137](file://app/Ai/Agents/DevBot.php#L30-L137)
- [McpClientService.php:20-279](file://app/Services/McpClientService.php#L20-L279)

## Architecture Overview
The system adheres to layered architecture with clear boundaries:
- Presentation: Controllers and ViewModels
- Application: Actions implementing business capabilities
- Domain: Models with strong typing via Enums
- Infrastructure: AI Agent and MCP client service
- External Systems: Laravel AI, MCP tools, and third-party services

```mermaid
classDiagram
class BaseAction {
+run(callback) mixed
+handleException(exception) never
}
class ChatController {
+show(conversation, listAction) View
+listConversations(listAction) JsonResponse
+createConversation(request, action) JsonResponse
+getConversation(conversation, action) JsonResponse
+sendMessage(request, action) JsonResponse|RedirectResponse
}
class ChatViewModel {
+getCurrentConversation() Conversation?
+getFormattedMessages() Collection
+getSidebarConversations() Collection
+getCurrentConversationId() int?
+getCurrentConversationTitle() string?
}
class Conversation {
+messages() HasMany
+generateTitleFromFirstMessage(message) void
+getRecentMessagesAttribute() Collection
+getMessagesForAgent() array
}
class Message {
+conversation() BelongsTo
+isUserMessage() bool
+formattedTimestamp() string
+formattedContent() string
}
class ConversationStatus {
<<enumeration>>
+label() string
+color() string
+icon() string
+isActive() bool
+isArchived() bool
+isDeleted() bool
}
class MessageRole {
<<enumeration>>
+label() string
+color() string
+icon() string
+isUser() bool
+isAssistant() bool
}
class DevBot {
+model() string
+instructions() string|Stringable
+messages() iterable
+tools() iterable
}
class McpClientService {
+initialize() void
+callTool(toolName, arguments) string
+disconnect() void
+isConnected() bool
+getClient() Client?
+terminate() void
}
ChatController --> BaseAction : "delegates"
ChatController --> ChatViewModel : "uses"
ChatViewModel --> Conversation : "formats"
ChatViewModel --> Message : "formats"
Conversation --> ConversationStatus : "casts"
Message --> MessageRole : "casts"
DevBot --> McpClientService : "uses"
```

**Diagram sources**
- [BaseAction.php:28-58](file://app/Actions/BaseAction.php#L28-L58)
- [ChatController.php:19-154](file://app/Http/Controllers/ChatController.php#L19-L154)
- [ChatViewModel.php:29-120](file://app/ViewModels/ChatViewModel.php#L29-L120)
- [Conversation.php:9-51](file://app/Models/Conversation.php#L9-L51)
- [Message.php:10-45](file://app/Models/Message.php#L10-L45)
- [ConversationStatus.php:23-89](file://app/Enums/ConversationStatus.php#L23-L89)
- [MessageRole.php:23-77](file://app/Enums/MessageRole.php#L23-L77)
- [DevBot.php:30-137](file://app/Ai/Agents/DevBot.php#L30-L137)
- [McpClientService.php:20-279](file://app/Services/McpClientService.php#L20-L279)

## Detailed Component Analysis

### Business Logic Encapsulation with Actions
Actions encapsulate single-responsibility business operations with consistent error handling. They provide a uniform execution pattern and enable dependency injection over helper functions.

```mermaid
sequenceDiagram
participant Client as "Client"
participant Controller as "ChatController"
participant Action as "CreateConversationAction"
participant Model as "Conversation Model"
Client->>Controller : "POST /chat/conversations"
Controller->>Controller : "Validate request"
Controller->>Action : "Execute with ConversationData"
Action->>Action : "run(callback)"
Action->>Model : "Create conversation"
Model-->>Action : "Conversation instance"
Action-->>Controller : "Conversation"
Controller-->>Client : "JSON response"
```

**Diagram sources**
- [ChatController.php:67-84](file://app/Http/Controllers/ChatController.php#L67-L84)
- [BaseAction.php:49-56](file://app/Actions/BaseAction.php#L49-L56)
- [Conversation.php:9-51](file://app/Models/Conversation.php#L9-L51)

**Section sources**
- [BaseAction.php:28-58](file://app/Actions/BaseAction.php#L28-L58)
- [ChatController.php:67-84](file://app/Http/Controllers/ChatController.php#L67-L84)

### Data Transfer Objects and Standardized Responses
DTOs ensure immutable data transfer between layers, while ApiResponseData standardizes API responses across the application.

```mermaid
classDiagram
class ApiResponseData {
+bool success
+mixed data
+?string message
+array meta
+int statusCode
+success(data, message, meta, statusCode) ApiResponseData
+error(message, statusCode, data, meta) ApiResponseData
+toArray() array
}
class ConversationData {
+?string title
+?string initialMessage
+fromRequest(request) ConversationData
+fromMessage(message) ConversationData
}
class MessageData {
+string content
+?int conversationId
+fromRequest(request) MessageData
}
ApiResponseData <|-- SuccessResponse
ApiResponseData <|-- ErrorResponse
```

**Diagram sources**
- [ApiResponseData.php:31-90](file://app/DTOs/ApiResponseData.php#L31-L90)
- [ConversationData.php:29-58](file://app/DTOs/ConversationData.php#L29-L58)
- [MessageData.php:29-47](file://app/DTOs/MessageData.php#L29-L47)

**Section sources**
- [ApiResponseData.php:31-90](file://app/DTOs/ApiResponseData.php#L31-L90)
- [ConversationData.php:29-58](file://app/DTOs/ConversationData.php#L29-L58)
- [MessageData.php:29-47](file://app/DTOs/MessageData.php#L29-L47)

### Strong Typing with Enums
Enums provide compile-time safety and metadata methods for UI rendering and filtering.

```mermaid
classDiagram
class ConversationStatus {
<<enumeration>>
+Active
+Archived
+Deleted
+label() string
+color() string
+icon() string
+isActive() bool
+isArchived() bool
+isDeleted() bool
}
class MessageRole {
<<enumeration>>
+User
+Assistant
+label() string
+color() string
+icon() string
+isUser() bool
+isAssistant() bool
}
ConversationStatus --> Conversation : "casts status"
MessageRole --> Message : "casts role"
```

**Diagram sources**
- [ConversationStatus.php:23-89](file://app/Enums/ConversationStatus.php#L23-L89)
- [MessageRole.php:23-77](file://app/Enums/MessageRole.php#L23-L77)
- [Conversation.php:17-19](file://app/Models/Conversation.php#L17-L19)
- [Message.php:18-20](file://app/Models/Message.php#L18-L20)

**Section sources**
- [ConversationStatus.php:23-89](file://app/Enums/ConversationStatus.php#L23-L89)
- [MessageRole.php:23-77](file://app/Enums/MessageRole.php#L23-L77)

### Presentation Layer with ViewModels
ViewModels centralize presentation logic, keeping controllers thin and views simple.

```mermaid
flowchart TD
Start(["ViewModel Construction"]) --> CheckConv["Check Current Conversation"]
CheckConv --> FormatMsgs["Format Messages Collection"]
FormatMsgs --> MapFields["Map Fields: id, role, content, timestamp"]
MapFields --> CheckRole{"Is Assistant Message?"}
CheckRole --> |Yes| FormatMd["Apply Markdown Formatting"]
CheckRole --> |No| UseRaw["Use Raw Content"]
FormatMd --> BuildMsg["Build Message Array"]
UseRaw --> BuildMsg
BuildMsg --> Sidebar["Format Sidebar Conversations"]
Sidebar --> MapSidebar["Map: title, timestamps, active state"]
MapSidebar --> Return["Return Prepared Data"]
```

**Diagram sources**
- [ChatViewModel.php:59-102](file://app/ViewModels/ChatViewModel.php#L59-L102)

**Section sources**
- [ChatViewModel.php:29-120](file://app/ViewModels/ChatViewModel.php#L29-L120)

### AI Agent Integration and MCP Tool Connectivity
DevBot integrates with Laravel AI and MCP tools, while McpClientService manages connection lifecycle and reliability.

```mermaid
sequenceDiagram
participant Controller as "ChatController"
participant Agent as "DevBot"
participant McpSvc as "McpClientService"
participant Tool as "MCP Tool"
Controller->>Agent : "Send user message"
Agent->>Agent : "Prepare conversation context"
Agent->>McpSvc : "Call tool with arguments"
loop "Retry Attempts"
McpSvc->>McpSvc : "initialize()"
McpSvc->>Tool : "callTool(name, args)"
Tool-->>McpSvc : "result"
alt "Success"
McpSvc-->>Agent : "text content"
else "Failure"
McpSvc->>McpSvc : "disconnect() and retry"
end
end
Agent-->>Controller : "Assistant response"
Controller-->>Controller : "Format response"
```

**Diagram sources**
- [ChatController.php:117-152](file://app/Http/Controllers/ChatController.php#L117-L152)
- [DevBot.php:109-135](file://app/Ai/Agents/DevBot.php#L109-L135)
- [McpClientService.php:110-179](file://app/Services/McpClientService.php#L110-L179)

**Section sources**
- [DevBot.php:30-137](file://app/Ai/Agents/DevBot.php#L30-L137)
- [McpClientService.php:20-279](file://app/Services/McpClientService.php#L20-L279)

### Conceptual Overview
The architecture emphasizes:
- Separation of concerns through Actions, DTOs, Enums, and ViewModels
- Consistent error handling via BaseAction
- Strong typing for domain values
- Thin controllers delegating to Actions
- Centralized presentation logic in ViewModels
- Reliable AI tool integration with MCP client service

```mermaid
graph TB
Standards["Architectural Standards"] --> Actions["Single-Purpose Actions"]
Standards --> DTOs["Immutable DTOs"]
Standards --> Enums["Strongly-Typed Enums"]
Standards --> VM["ViewModels for Presentation"]
Standards --> AI["AI Agent Integration"]
Standards --> MCP["MCP Client Service"]
Actions --> Consistency["Consistent Error Handling"]
DTOs --> Predictability["Predictable Data Flow"]
Enums --> Safety["Compile-Time Safety"]
VM --> Maintainability["Maintainable Views"]
AI --> Extensibility["Extensible Tooling"]
MCP --> Reliability["Reliable Connections"]
```

[No sources needed since this diagram shows conceptual workflow, not actual code structure]

## Dependency Analysis
The application maintains low coupling and high cohesion through clear interfaces and dependency inversion:

```mermaid
graph TB
subgraph "External Dependencies"
Laravel["laravel/framework"]
AI["laravel/ai"]
CommonMark["league/commonmark"]
MCP["php-mcp/client"]
end
subgraph "Internal Components"
Controller["ChatController"]
Actions["Actions Layer"]
Models["Models"]
DTOs["DTOs"]
Enums["Enums"]
VM["ViewModels"]
Agent["DevBot"]
Service["McpClientService"]
end
Controller --> Actions
Actions --> Models
Actions --> DTOs
Models --> Enums
Controller --> VM
Agent --> Service
Service --> MCP
Controller --> Laravel
Agent --> AI
VM --> CommonMark
```

**Diagram sources**
- [composer.json:11-28](file://composer.json#L11-L28)
- [ChatController.php:5-17](file://app/Http/Controllers/ChatController.php#L5-L17)
- [DevBot.php:5-24](file://app/Ai/Agents/DevBot.php#L5-L24)
- [McpClientService.php:5-11](file://app/Services/McpClientService.php#L5-L11)

**Section sources**
- [composer.json:11-28](file://composer.json#L11-L28)
- [composer.json:83-94](file://composer.json#L83-L94)

## Performance Considerations
- Database performance: Eager load relations to prevent N+1 queries; use appropriate indexing
- Caching strategies: Leverage Cache::remember and Cache::lock for race conditions
- Query optimization: Use whereBelongsTo, cursor() for memory efficiency, and selective column retrieval
- HTTP client: Set explicit timeouts and retry with exponential backoff
- Queue and jobs: Configure retry_after greater than job timeout; use ShouldBeUnique for deduplication
- Task scheduling: Use withoutOverlapping() and onOneServer() for distributed environments
- String handling: Prefer mb_* functions for UTF-8 safety

[No sources needed since this section provides general guidance]

## Troubleshooting Guide
Common issues and resolutions:
- Action execution failures: BaseAction.run() wraps callbacks and delegates to handleException(); ensure proper exception propagation
- MCP client connectivity: McpClientService.initialize() handles connection lifecycle; verify configuration and retry logic
- AI tool failures: DevBot.tools() provides available tools; check tool availability and argument formatting
- Database query performance: Use chunk()/chunkById() for large datasets; implement proper indexing
- API response formatting: ApiResponseData ensures consistent response structure; validate success/error patterns

**Section sources**
- [BaseAction.php:36-56](file://app/Actions/BaseAction.php#L36-L56)
- [McpClientService.php:48-96](file://app/Services/McpClientService.php#L48-L96)
- [DevBot.php:123-135](file://app/Ai/Agents/DevBot.php#L123-L135)
- [ApiResponseData.php:31-90](file://app/DTOs/ApiResponseData.php#L31-L90)

## Conclusion
The Laravel Assistant enforces architectural standards through consistent patterns: single-purpose Actions, immutable DTOs, strongly-typed Enums, ViewModel-driven presentation, and robust AI/MCP integration. These standards ensure maintainability, testability, and scalability while aligning with Laravel best practices and the project's AI-first design goals.