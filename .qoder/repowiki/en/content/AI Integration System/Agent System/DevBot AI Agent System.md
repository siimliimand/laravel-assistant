# DevBot AI Agent System

<cite>
**Referenced Files in This Document**
- [DevBot.php](file://app/Ai/Agents/DevBot.php)
- [ChatController.php](file://app/Http/Controllers/ChatController.php)
- [Conversation.php](file://app/Models/Conversation.php)
- [Message.php](file://app/Models/Message.php)
- [DatabaseQueryTool.php](file://app/Ai/Tools/DatabaseQueryTool.php)
- [DatabaseSchemaTool.php](file://app/Ai/Tools/DatabaseSchemaTool.php)
- [SearchDocsTool.php](file://app/Ai/Tools/SearchDocsTool.php)
- [TinkerTool.php](file://app/Ai/Tools/TinkerTool.php)
- [FileSystemTool.php](file://app/Ai/Tools/FileSystemTool.php)
- [GitTool.php](file://app/Ai/Tools/GitTool.php)
- [GitHubTool.php](file://app/Ai/Tools/GitHubTool.php)
- [OpenSpecTool.php](file://app/Ai/Tools/OpenSpecTool.php)
- [McpClientService.php](file://app/Services/McpClientService.php)
- [Markdown.php](file://app/Helpers/Markdown.php)
- [ai.php](file://config/ai.php)
- [services.php](file://config/services.php)
- [web.php](file://routes/web.php)
- [chat.blade.php](file://resources/views/chat.blade.php)
- [2026_04_02_123216_create_conversations_table.php](file://database/migrations/2026_04_02_123216_create_conversations_table.php)
- [2026_04_02_123238_create_messages_table.php](file://database/migrations/2026_04_02_123238_create_messages_table.php)
- [composer.json](file://composer.json)
- [AppServiceProvider.php](file://app/Providers/AppServiceProvider.php)
- [AGENTS.md](file://AGENTS.md)
- [SKILL.md](file://.agents/skills/project-creation/SKILL.md)
</cite>

## Update Summary
**Changes Made**
- Enhanced DevBot agent with comprehensive project creation capabilities and expanded tool suite
- Added four new MCP-powered tools: FileSystemTool, GitTool, GitHubTool, and OpenSpecTool
- Integrated Project Creation skill for orchestrating complete micro-SaaS project workflows
- Updated agent instructions to include project creation workflow and tool access
- Added project configuration in ai.php with base_path, GitHub token, and Git settings
- Enhanced security considerations with project isolation and path validation
- Updated system architecture to include project orchestration layer

## Table of Contents
1. [Introduction](#introduction)
2. [System Architecture](#system-architecture)
3. [Core Components](#core-components)
4. [Agent Implementation](#agent-implementation)
5. [Project Creation Capabilities](#project-creation-capabilities)
6. [Expanded Tool Suite](#expanded-tool-suite)
7. [MCP Tool Integration](#mcp-tool-integration)
8. [MCP Client Service](#mcp-client-service)
9. [Conversation Management](#conversation-management)
10. [User Interface](#user-interface)
11. [AI Provider Configuration](#ai-provider-configuration)
12. [Skills and Capabilities](#skills-and-capabilities)
13. [Database Schema](#database-schema)
14. [API Endpoints](#api-endpoints)
15. [Error Handling](#error-handling)
16. [Security Considerations](#security-considerations)
17. [Performance Considerations](#performance-considerations)
18. [Deployment and Setup](#deployment-and-setup)
19. [Conclusion](#conclusion)

## Introduction

DevBot is an AI-powered development assistant integrated into a Laravel application with comprehensive MCP (Model Context Protocol) tool integration and advanced project creation capabilities. This intelligent chat system provides developers with instant access to programming knowledge, code review capabilities, debugging assistance, architectural guidance, direct database interaction, and complete project orchestration through specialized MCP-powered tools. Built with Laravel's AI framework and enhanced with MCP protocol communication, DevBot serves as a comprehensive development companion that understands Laravel and PHP best practices while offering real-time conversational AI responses with powerful tool execution capabilities.

The system combines modern AI technologies with Laravel's robust framework and MCP protocol to create an intuitive development environment where developers can ask questions, receive code examples, get guidance on best practices, directly interact with their application's database and documentation systems, and orchestrate complete micro-SaaS project creation workflows through secure MCP tool proxies. DevBot is particularly focused on Laravel ecosystem development, making it an invaluable tool for PHP developers working within the Laravel framework.

## System Architecture

The DevBot system follows a clean, layered architecture that separates concerns between presentation, business logic, data persistence, AI integration, MCP tool execution, and comprehensive project orchestration. The architecture is designed around Laravel's MVC pattern while incorporating modern AI agent capabilities, extensive MCP tool integration, and secure external service communication for project management.

```mermaid
graph TB
subgraph "Presentation Layer"
UI[Web Interface]
Blade[Blade Templates]
end
subgraph "Application Layer"
Controller[ChatController]
Middleware[Request Validation]
Agent[DevBot Agent]
Conversation[Conversation Management]
Message[Message Processing]
end
subgraph "AI Integration Layer"
AI_Provider[AI Provider Interface]
Tools[MCP Tool Proxies]
ProjectTools[Project Creation Tools]
end
subgraph "Project Orchestration Layer"
FileSystemTool[FileSystemTool]
GitTool[GitTool]
GitHubTool[GitHubTool]
OpenSpecTool[OpenSpecTool]
end
subgraph "MCP Communication Layer"
McpClientService[McpClientService]
BoostServer[Laravel Boost MCP Server]
end
subgraph "External Services"
Database[(Database)]
Docs[Laravel Documentation]
GitHub[(GitHub API)]
</subgraph>
UI --> Controller
Blade --> Controller
Controller --> Agent
Controller --> Conversation
Controller --> Message
Agent --> AI_Provider
Agent --> Tools
Agent --> ProjectTools
Tools --> McpClientService
ProjectTools --> McpClientService
McpClientService --> BoostServer
BoostServer --> Database
BoostServer --> Docs
BoostServer --> GitHub
Conversation --> Models[Eloquent Models]
Message --> Models
Models --> Database
AI_Provider --> Anthropic[Anthropic API]
AI_Provider --> Gemini[Gemini API]
AI_Provider --> Local[Local Models]
```

**Diagram sources**
- [ChatController.php:13-113](file://app/Http/Controllers/ChatController.php#L13-L113)
- [DevBot.php:20-135](file://app/Ai/Agents/DevBot.php#L20-L135)
- [McpClientService.php:20-279](file://app/Services/McpClientService.php#L20-L279)
- [AppServiceProvider.php:9-65](file://app/Providers/AppServiceProvider.php#L9-L65)

The architecture ensures clear separation of concerns with the controller handling HTTP requests, the agent managing AI interactions and MCP tool proxy execution, the project orchestration layer providing comprehensive project management capabilities, and the models handling data persistence. The MCP tool integration layer provides secure, controlled access to application resources while maintaining system security boundaries through the McpClientService abstraction.

**Section sources**
- [ChatController.php:13-113](file://app/Http/Controllers/ChatController.php#L13-L113)
- [DevBot.php:20-135](file://app/Ai/Agents/DevBot.php#L20-L135)
- [McpClientService.php:20-279](file://app/Services/McpClientService.php#L20-L279)

## Core Components

### AI Agent System

The heart of DevBot is the DevBot AI agent, which implements Laravel's AI agent interface with comprehensive MCP tool proxy integration and expanded project creation capabilities. This agent is configured with specific parameters optimized for development assistance and includes eight specialized MCP-powered tools for enhanced functionality, including four new project creation tools.

```mermaid
classDiagram
class DevBot {
+Conversation conversation
+model() string
+instructions() Stringable|string
+messages() iterable
+tools() iterable
-getMessagesForAgent() array
}
class Agent {
<<interface>>
+model() string
+instructions() Stringable|string
+messages() iterable
+tools() iterable
}
class Conversational {
<<interface>>
+messages() iterable
}
class HasTools {
<<interface>>
+tools() iterable
}
class DatabaseQueryTool {
+description() string
+handle(request) string
+schema(schema) array
}
class DatabaseSchemaTool {
+description() string
+handle(request) string
+schema(schema) array
}
class SearchDocsTool {
+description() string
+handle(request) string
+schema(schema) array
}
class TinkerTool {
+description() string
+handle(request) string
+schema(schema) array
}
class FileSystemTool {
+description() string
+handle(request) string
+schema(schema) array
-createProject(name) string
-writeFile(project, path, content) string
-readFile(project, path) string
-listFiles(project) string
-projectExists(project) string
}
class GitTool {
+description() string
+handle(request) string
+schema(schema) array
-init(project) string
-add(project, files) string
-commit(project, message) string
-remoteAdd(project, name, url) string
-push(project, branch) string
-status(project) string
}
class GitHubTool {
+description() string
+handle(request) string
+schema(schema) array
-createRepo(name, private) string
-getRepoInfo(name) string
-validateToken() string
}
class OpenSpecTool {
+description() string
+handle(request) string
+schema(schema) array
-getStatus(project) string
-getInstructions(project) string
}
class McpClientService {
+initialize() void
+callTool(toolName, arguments) string
+disconnect() void
+isConnected() bool
}
DevBot ..|> Agent
DevBot ..|> Conversational
DevBot ..|> HasTools
DevBot --> DatabaseQueryTool
DevBot --> DatabaseSchemaTool
DevBot --> SearchDocsTool
DevBot --> TinkerTool
DevBot --> FileSystemTool
DevBot --> GitTool
DevBot --> GitHubTool
DevBot --> OpenSpecTool
DatabaseQueryTool --> McpClientService
DatabaseSchemaTool --> McpClientService
SearchDocsTool --> McpClientService
TinkerTool --> McpClientService
FileSystemTool --> McpClientService
GitTool --> McpClientService
GitHubTool --> McpClientService
OpenSpecTool --> McpClientService
```

**Diagram sources**
- [DevBot.php:24-135](file://app/Ai/Agents/DevBot.php#L24-L135)
- [DatabaseQueryTool.php:13-84](file://app/Ai/Tools/DatabaseQueryTool.php#L13-L84)
- [DatabaseSchemaTool.php:13-69](file://app/Ai/Tools/DatabaseSchemaTool.php#L13-L69)
- [SearchDocsTool.php:13-75](file://app/Ai/Tools/SearchDocsTool.php#L13-L75)
- [TinkerTool.php:13-89](file://app/Ai/Tools/TinkerTool.php#L13-L89)
- [FileSystemTool.php:14-298](file://app/Ai/Tools/FileSystemTool.php#L14-L298)
- [GitTool.php:14-324](file://app/Ai/Tools/GitTool.php#L14-L324)
- [GitHubTool.php:13-224](file://app/Ai/Tools/GitHubTool.php#L13-L224)
- [OpenSpecTool.php:12-184](file://app/Ai/Tools/OpenSpecTool.php#L12-L184)
- [McpClientService.php:20-279](file://app/Services/McpClientService.php#L20-L279)

The agent is configured with a maximum step limit of 10 and a temperature setting of 0.7, providing balanced responses that are both helpful and accurate for development scenarios. The eight integrated MCP tool proxies provide comprehensive development assistance capabilities through secure external service communication, including four new project creation tools for complete workflow orchestration.

**Section sources**
- [DevBot.php:24-135](file://app/Ai/Agents/DevBot.php#L24-L135)

### Controller Layer

The ChatController serves as the primary entry point for user interactions, handling both web interface rendering and API requests. It manages conversation lifecycle, validates user input, coordinates with the AI agent for responses, and integrates with the MCP tool proxy system for enhanced functionality including project creation workflows.

```mermaid
sequenceDiagram
participant User as User Browser
participant Controller as ChatController
participant Agent as DevBot Agent
participant Tools as MCP Tool Proxies
participant ProjectTools as Project Creation Tools
participant McpService as McpClientService
participant Boost as Laravel Boost Server
User->>Controller : POST /chat/message
Controller->>Controller : Validate Request
Controller->>DB : Create/Load Conversation
Controller->>DB : Save User Message
Controller->>Agent : prompt(user_message)
Agent->>Tools : Execute Tool Requests
Agent->>ProjectTools : Execute Project Creation Workflow
Tools->>McpService : callTool(tool_name, arguments)
ProjectTools->>McpService : callTool(tool_name, arguments)
McpService->>Boost : JSON-RPC 2.0 request
Boost-->>McpService : Tool response
McpService-->>Tools : Formatted result
McpService-->>ProjectTools : Formatted result
Tools-->>Agent : Tool Results
ProjectTools-->>Agent : Project Workflow Results
Agent->>Agent : Generate AI Response
Agent-->>Controller : Response Text
Controller->>DB : Save Assistant Message
Controller-->>User : JSON Response
Note over Controller,DB : Conversation and Messages stored
```

**Diagram sources**
- [ChatController.php:39-113](file://app/Http/Controllers/ChatController.php#L39-L113)

**Section sources**
- [ChatController.php:39-113](file://app/Http/Controllers/ChatController.php#L39-L113)

## Agent Implementation

### Configuration and Behavior

The DevBot agent is configured with specific parameters that optimize its behavior for development assistance and includes comprehensive instructions for appropriate responses. The agent uses environment variables for flexible deployment configurations and includes eight specialized MCP-powered tools for enhanced functionality, including four new project creation tools.

```mermaid
flowchart TD
Start([Agent Initialization]) --> LoadConfig["Load Environment Config"]
LoadConfig --> SetModel["Set Model: claude-haiku-4-5-20251001"]
SetModel --> SetTemperature["Set Temperature: 0.7"]
SetTemperature --> SetMaxSteps["Set Max Steps: 10"]
SetMaxSteps --> LoadTools["Load MCP Tool Proxies"]
LoadTools --> LoadProjectTools["Load Project Creation Tools"]
LoadProjectTools --> Ready([Agent Ready])
Ready --> ProcessMessage["Process User Message"]
ProcessMessage --> ValidateInput["Validate Input"]
ValidateInput --> InputValid{"Valid Input?"}
InputValid --> |No| ReturnError["Return Error Response"]
InputValid --> |Yes| CheckTools["Check Tool Requests"]
CheckTools --> CheckProjectWorkflow["Check Project Creation Request"]
CheckProjectWorkflow --> ToolRequested{"Tool Request?"}
ToolRequested --> |Yes| ExecuteTool["Execute MCP Tool Proxy"]
ToolRequested --> |No| GenerateResponse["Generate AI Response"]
ExecuteTool --> ToolResult["Process Tool Result"]
ToolResult --> GenerateResponse
GenerateResponse --> FormatResponse["Format Response"]
FormatResponse --> ReturnResponse["Return Response"]
ReturnError --> End([End])
ReturnResponse --> End
```

**Diagram sources**
- [DevBot.php:28-38](file://app/Ai/Agents/DevBot.php#L28-L38)
- [DevBot.php:47-100](file://app/Ai/Agents/DevBot.php#L47-L100)
- [DevBot.php:121-135](file://app/Ai/Agents/DevBot.php#L121-L135)
- [DevBot.php:107-114](file://app/Ai/Agents/DevBot.php#L107-L114)

The agent's instructions emphasize development-focused assistance, including Laravel and PHP best practices, code review capabilities, architectural guidance, MCP tool usage, and comprehensive project creation workflows. This ensures responses remain relevant and helpful for developer use cases while leveraging the power of integrated MCP tool proxies including four new project creation tools.

**Section sources**
- [DevBot.php:28-135](file://app/Ai/Agents/DevBot.php#L28-L135)

## Project Creation Capabilities

### Project Creation Workflow

DevBot now includes comprehensive project creation capabilities that enable developers to transform micro-SaaS ideas into complete, structured projects with automated orchestration. The system guides users through a seven-step workflow that covers requirements gathering, project setup, specification creation, version control initialization, and remote repository creation.

```mermaid
flowchart TD
UserIdea["User Describes Project Idea"] --> GatherRequirements["Gather Requirements"]
GatherRequirements --> CreateProject["Create Project Directory"]
CreateProject --> GenerateSpecs["Generate OpenSpec Artifacts"]
GenerateSpecs --> InitGit["Initialize Git Repository"]
InitGit --> CreateGitHubRepo["Create GitHub Repository"]
CreateGitHubRepo --> PushToGitHub["Push to GitHub"]
PushToGitHub --> ProvideSummary["Provide Completion Summary"]
GatherRequirements --> AskQuestions["Ask Clarifying Questions"]
AskQuestions --> ConfirmFeatures["Confirm Features & Requirements"]
ConfirmFeatures --> SuggestName["Suggest Kebab-Case Project Name"]
SuggestName --> ValidateName["Validate Project Name"]
ValidateName --> CreateProject
CreateProject --> ValidatePath["Validate Path Scoping"]
ValidatePath --> CreateDirectory["Create Directory with 0755 Permissions"]
CreateDirectory --> VerifyCreation["Verify Directory Creation"]
GenerateSpecs --> CheckArtifacts["Check OpenSpec Artifacts"]
CheckArtifacts --> ProposeArtifacts["Run openspec-propose Skill"]
ProposeArtifacts --> VerifyArtifacts["Verify Artifact Creation"]
VerifyArtifacts --> ReviewArtifacts["Review with User"]
InitGit --> CheckGit["Check Git Availability"]
CheckGit --> InitRepo["Initialize Repository with Default Branch"]
InitRepo --> StageFiles["Stage All Files"]
StageFiles --> CommitFiles["Commit with Descriptive Message"]
CommitFiles --> VerifyCommit["Verify Commit Success"]
CreateGitHubRepo --> ValidateToken["Validate GitHub Token"]
ValidateToken --> CreateRepo["Create Repository via API"]
CreateRepo --> StoreURLs["Store Repository URLs"]
PushToGitHub --> AddRemote["Add GitHub Remote"]
AddRemote --> PushFiles["Push to Origin Main"]
PushFiles --> ProvideURL["Provide Repository URL"]
ProvideSummary --> OfferNextSteps["Offer Next Steps with /opsx:apply"]
OfferNextSteps --> ProvideDocs["Provide Documentation Links"]
```

**Diagram sources**
- [SKILL.md:15-216](file://.agents/skills/project-creation/SKILL.md#L15-L216)

The project creation workflow is guided by the Project Creation skill which provides detailed step-by-step instructions and error recovery procedures. The system ensures security through path scoping, validates all inputs, and provides comprehensive error handling for each step of the process.

**Section sources**
- [SKILL.md:15-216](file://.agents/skills/project-creation/SKILL.md#L15-L216)

### Project Configuration

The system includes comprehensive project configuration through the ai.php configuration file, providing secure storage paths, GitHub integration settings, and Git repository defaults for project creation workflows.

**Section sources**
- [ai.php:52-56](file://config/ai.php#L52-L56)

## Expanded Tool Suite

### File System Management Tool

The FileSystemTool provides secure, scoped file system operations for project management within the `storage/projects/` directory. It enforces strict security policies and provides comprehensive error handling for file operations via the Laravel Boost MCP server.

```mermaid
flowchart TD
FileSystemRequest["File System Request"] --> ValidateAction["Validate Action Type"]
ValidateAction --> CheckAction{"Action Type?"}
CheckAction --> |createProject| ValidateProjectName["Validate Project Name"]
CheckAction --> |writeFile| ValidateWriteParams["Validate Project & Path"]
CheckAction --> |readFile| ValidateReadParams["Validate Project & Path"]
CheckAction --> |listFiles| ValidateListParams["Validate Project"]
CheckAction --> |projectExists| ValidateExistsParams["Validate Project"]
ValidateProjectName --> CheckNameFormat["Check Kebab-Case Format"]
CheckNameFormat --> CheckExists{"Project Exists?"}
CheckExists --> |Yes| SuggestAlternative["Suggest Alternative Name"]
CheckExists --> |No| CreateDirectory["Create Directory with 0755 Permissions"]
CreateDirectory --> LogCreation["Log Project Creation"]
LogCreation --> ReturnSuccess["Return Success Message"]
ValidateWriteParams --> ValidatePath["Validate Path Scoping"]
ValidatePath --> CheckTraversal{"Path Safe?"}
CheckTraversal --> |No| BlockTraversal["Block Directory Traversal"]
CheckTraversal --> |Yes| CreateParentDirs["Create Parent Directories"]
CreateParentDirs --> WriteFile["Write File Content"]
WriteFile --> LogWrite["Log File Write"]
LogWrite --> ReturnWriteSuccess["Return Success Message"]
ValidateReadParams --> ValidateReadPath["Validate Path Scoping"]
ValidateReadPath --> CheckFileExists{"File Exists?"}
CheckFileExists --> |No| ReturnNotFound["Return Not Found Error"]
CheckFileExists --> |Yes| CheckIsFile{"Is File?"}
CheckIsFile --> |No| ReturnIsDirectory["Return Directory Error"]
CheckIsFile --> |Yes| ReadFile["Read File Content"]
ReadFile --> ReturnFileContent["Return File Content"]
ValidateListParams --> CheckProjectExists{"Project Exists?"}
CheckProjectExists --> |No| ReturnProjectError["Return Project Error"]
CheckProjectExists --> |Yes| ListProjectFiles["List All Project Files"]
ListProjectFiles --> ReturnFileList["Return File List JSON"]
ValidateExistsParams --> CheckExistsPath["Validate Path Scoping"]
CheckExistsPath --> CheckExistsFinal{"Project Exists?"}
CheckExistsFinal --> ReturnExistsJSON["Return Exists JSON"]
```

**Diagram sources**
- [FileSystemTool.php:34-298](file://app/Ai/Tools/FileSystemTool.php#L34-L298)

The tool enforces strict security policies including kebab-case project naming, path traversal prevention, and scoped access to the `storage/projects/` directory. All operations are logged for security auditing and compliance.

**Section sources**
- [FileSystemTool.php:14-298](file://app/Ai/Tools/FileSystemTool.php#L14-L298)

### Git Version Control Tool

The GitTool provides comprehensive Git operations for project version control management within the `storage/projects/` directory. It integrates with the Symfony Process component for secure command execution and provides detailed error handling for Git operations.

```mermaid
flowchart TD
GitRequest["Git Request"] --> CheckGitAvailability["Check Git Availability"]
CheckGitAvailability --> GitAvailable{"Git Installed?"}
GitAvailable --> |No| ReturnGitError["Return Git Not Installed Error"]
GitAvailable --> |Yes| ValidateAction["Validate Git Action"]
ValidateAction --> CheckAction{"Git Action?"}
CheckAction --> |init| ValidateProject["Validate Project Path"]
CheckAction --> |add| ValidateAddParams["Validate Project & Files"]
CheckAction --> |commit| ValidateCommitParams["Validate Project & Message"]
CheckAction --> |remoteAdd| ValidateRemoteParams["Validate Project & URL"]
CheckAction --> |push| ValidatePushParams["Validate Project"]
CheckAction --> |status| ValidateStatusParams["Validate Project"]
ValidateProject --> CheckProjectPath["Check Project Path Safety"]
CheckProjectPath --> InitRepo["Initialize Git Repository"]
InitRepo --> ConfigureUser["Configure Git User"]
ConfigureUser --> LogInit["Log Repository Initialization"]
LogInit --> ReturnInitSuccess["Return Success Message"]
ValidateAddParams --> CheckProjectPathAdd["Check Project Path Safety"]
CheckProjectPathAdd --> StageFiles["Stage Files with Git Add"]
StageFiles --> LogStage["Log File Staging"]
LogStage --> ReturnStageSuccess["Return Success Message"]
ValidateCommitParams --> CheckProjectPathCommit["Check Project Path Safety"]
CheckProjectPathCommit --> CommitFiles["Commit with Git Commit"]
CommitFiles --> LogCommit["Log Commit"]
LogCommit --> ReturnCommitSuccess["Return Success Message"]
ValidateRemoteParams --> CheckProjectPathRemote["Check Project Path Safety"]
CheckProjectPathRemote --> AddRemote["Add Remote with Git Remote Add"]
AddRemote --> LogRemote["Log Remote Addition"]
LogRemote --> ReturnRemoteSuccess["Return Success Message"]
ValidatePushParams --> CheckProjectPathPush["Check Project Path Safety"]
CheckProjectPathPush --> PushFiles["Push with Git Push"]
PushFiles --> LogPush["Log Push Operation"]
LogPush --> ReturnPushSuccess["Return Success Message"]
ValidateStatusParams --> CheckProjectPathStatus["Check Project Path Safety"]
CheckProjectPathStatus --> GetStatus["Get Repository Status"]
GetStatus --> ReturnStatus["Return Status Output"]
```

**Diagram sources**
- [GitTool.php:34-324](file://app/Ai/Tools/GitTool.php#L34-L324)

The tool integrates with the Symfony Process component for secure command execution, validates Git availability, and provides comprehensive error handling for all Git operations. It automatically configures Git user settings and enforces project path safety.

**Section sources**
- [GitTool.php:14-324](file://app/Ai/Tools/GitTool.php#L14-L324)

### GitHub Integration Tool

The GitHubTool provides comprehensive GitHub API integration for repository creation, management, and authentication validation. It handles GitHub API authentication, error handling, and provides detailed repository information retrieval.

```mermaid
flowchart TD
GitHubRequest["GitHub Request"] --> ValidateToken["Validate GitHub Token"]
ValidateToken --> TokenConfigured{"Token Configured?"}
TokenConfigured --> |No| ReturnTokenError["Return Token Not Configured Error"]
TokenConfigured --> |Yes| ValidateAction["Validate GitHub Action"]
ValidateAction --> CheckAction{"GitHub Action?"}
CheckAction --> |createRepo| ValidateRepoParams["Validate Repository Parameters"]
CheckAction --> |getRepoInfo| ValidateRepoInfoParams["Validate Repository Name"]
CheckAction --> |validateToken| ValidateTokenOnly["Validate Token Only"]
ValidateRepoParams --> CheckRepoName["Validate Repository Name"]
CheckRepoName --> CallGitHubAPI["Call GitHub API to Create Repository"]
CallGitHubAPI --> CheckAPISuccess{"API Call Success?"}
CheckAPISuccess --> |No| HandleAPIError["Handle GitHub API Error"]
CheckAPISuccess --> |Yes| ParseRepoData["Parse Repository Data"]
ParseRepoData --> LogRepoCreation["Log Repository Creation"]
LogRepoCreation --> ReturnRepoSuccess["Return Repository JSON"]
ValidateRepoInfoParams --> CheckRepoInfoName["Validate Repository Name"]
CheckRepoInfoName --> CallGitHubRepoAPI["Call GitHub API for Repository Info"]
CallGitHubRepoAPI --> CheckRepoInfoSuccess{"API Call Success?"}
CheckRepoInfoSuccess --> |No| HandleRepoInfoError["Handle Repository Info Error"]
CheckRepoInfoSuccess --> |Yes| ParseRepoInfo["Parse Repository Information"]
ParseRepoInfo --> ReturnRepoInfo["Return Repository Information JSON"]
ValidateTokenOnly --> CallTokenValidation["Call GitHub API for Token Validation"]
CallTokenValidation --> CheckTokenSuccess{"Token Validation Success?"}
CheckTokenSuccess --> |No| HandleTokenError["Handle Token Validation Error"]
CheckTokenSuccess --> |Yes| ParseTokenInfo["Parse Token Information"]
ParseTokenInfo --> LogTokenValidation["Log Token Validation"]
LogTokenValidation --> ReturnTokenValid["Return Token Valid JSON"]
HandleAPIError --> CheckAPIErrorType{"Check Error Type"}
CheckAPIErrorType --> |401| ReturnInvalidToken["Return Invalid Token Error"]
CheckAPIErrorType --> |403| ReturnRateLimit["Return Rate Limit Error"]
CheckAPIErrorType --> |422| CheckRepoExists["Check Repository Exists"]
CheckRepoExists --> |Exists| ReturnRepoExists["Return Repository Exists Error"]
CheckRepoExists --> |Not Exists| ReturnGenericError["Return Generic Error"]
```

**Diagram sources**
- [GitHubTool.php:33-224](file://app/Ai/Tools/GitHubTool.php#L33-L224)

The tool handles GitHub API authentication, validates tokens, manages repository creation with proper error handling, and provides comprehensive repository information retrieval. It includes specific error handling for common GitHub API scenarios including authentication failures, rate limiting, and repository conflicts.

**Section sources**
- [GitHubTool.php:13-224](file://app/Ai/Tools/GitHubTool.php#L13-L224)

### OpenSpec Management Tool

The OpenSpecTool provides comprehensive OpenSpec workflow management for project specification creation and validation. It integrates with the OpenSpec system to check artifact completion and provide workflow guidance for project specification management.

```mermaid
flowchart TD
OpenSpecRequest["OpenSpec Request"] --> ValidateAction["Validate OpenSpec Action"]
ValidateAction --> CheckAction{"OpenSpec Action?"}
CheckAction --> |getStatus| ValidateProject["Validate Project Name"]
CheckAction --> |getInstructions| ValidateProjectInstr["Validate Project Name"]
ValidateProject --> CheckProjectPath["Check Project Path"]
CheckProjectPath --> CheckOpenSpecDir{"OpenSpec Directory Exists?"}
CheckOpenSpecDir --> |No| ReturnMissingDir["Return Missing OpenSpec Directory Error"]
CheckOpenSpecDir --> |Yes| CheckRequiredArtifacts["Check Required Artifacts"]
CheckRequiredArtifacts --> CheckProposal["Check proposal.md"]
CheckProposal --> CheckDesign["Check design.md"]
CheckDesign --> CheckSpecs["Check specs/ directory"]
CheckSpecs --> CheckTasks["Check tasks.md"]
CheckTasks --> AggregateResults["Aggregate Artifact Status"]
AggregateResults --> CheckCompletion{"All Artifacts Complete?"}
CheckCompletion --> |Yes| ReturnComplete["Return Complete Status"]
CheckCompletion --> |No| ReturnIncomplete["Return Incomplete Status"]
ValidateProjectInstr --> CheckProjectInstrPath["Check Project Path"]
CheckProjectInstrPath --> ReturnInstructions["Return OpenSpec Instructions"]
```

**Diagram sources**
- [OpenSpecTool.php:32-184](file://app/Ai/Tools/OpenSpecTool.php#L32-L184)

The tool provides comprehensive OpenSpec workflow integration, checking for required artifacts (proposal.md, design.md, specs/ directory, tasks.md), validating artifact completion, and providing detailed workflow instructions for specification-driven development.

**Section sources**
- [OpenSpecTool.php:12-184](file://app/Ai/Tools/OpenSpecTool.php#L12-L184)

## MCP Tool Integration

### Database Query Tool

The DatabaseQueryTool provides secure, read-only SQL query execution against the application database through the MCP protocol. It enforces strict security policies and provides comprehensive error handling for database operations via the Laravel Boost MCP server.

```mermaid
flowchart TD
QueryRequest["SQL Query Request"] --> ValidateQuery["Validate Query Type"]
ValidateQuery --> CheckReadOnly{"Read-Only Allowed?"}
CheckReadOnly --> |No| BlockQuery["Block Non-Read-Only Query"]
CheckReadOnly --> |Yes| PrepareArgs["Prepare MCP Arguments"]
PrepareArgs --> CallMCP["Call McpClientService.callTool()"]
CallMCP --> ExecuteQuery["Execute via Laravel Boost Server"]
ExecuteQuery --> CheckResults{"Results Received?"}
CheckResults --> |Yes| FormatResults["Format Results"]
CheckResults --> |No| HandleError["Handle MCP Error"]
BlockQuery --> LogWarning["Log Security Warning"]
LogWarning --> ReturnError["Return Error Message"]
FormatResults --> Success["Success"]
HandleError --> ReturnError
ReturnError --> End([End])
Success --> End
```

**Diagram sources**
- [DatabaseQueryTool.php:26-69](file://app/Ai/Tools/DatabaseQueryTool.php#L26-L69)

The tool restricts queries to SELECT, SHOW, EXPLAIN, and DESCRIBE statements only, preventing destructive database operations. Results are automatically handled by the McpClientService and returned to the AI agent for processing.

**Section sources**
- [DatabaseQueryTool.php:13-84](file://app/Ai/Tools/DatabaseQueryTool.php#L13-L84)

### Database Schema Tool

The DatabaseSchemaTool provides comprehensive database schema information including table listings, column details, and index information through the MCP protocol. It offers both overview and detailed schema inspection capabilities via the Laravel Boost MCP server.

```mermaid
flowchart TD
SchemaRequest["Schema Request"] --> CheckTable{"Table Specified?"}
CheckTable --> |No| ListTables["List All Tables via MCP"]
CheckTable --> |Yes| CheckExists{"Table Exists?"}
CheckExists --> |No| TableError["Return Table Not Found Error"]
CheckExists --> |Yes| GetSchema["Get Table Schema via MCP"]
ListTables --> ReturnTables["Return Table List"]
GetSchema --> ProcessSchema["Process Schema via McpClientService"]
ProcessSchema --> ReturnSchema["Return Complete Schema"]
TableError --> End([End])
ReturnTables --> End
ReturnSchema --> End
```

**Diagram sources**
- [DatabaseSchemaTool.php:26-54](file://app/Ai/Tools/DatabaseSchemaTool.php#L26-L54)

The tool filters out internal database system tables and provides detailed information about table structure, column types, and index definitions for comprehensive database understanding through the MCP protocol.

**Section sources**
- [DatabaseSchemaTool.php:13-69](file://app/Ai/Tools/DatabaseSchemaTool.php#L13-L69)

### Search Documentation Tool

The SearchDocsTool provides Laravel and package documentation search capabilities with intelligent result deduplication and relevance scoring through the MCP protocol. It serves as a bridge between the AI agent and external documentation resources via the Laravel Boost MCP server.

```mermaid
flowchart TD
DocRequest["Documentation Request"] --> ValidateQueries["Validate Query Array"]
ValidateQueries --> CheckValid{"Valid Queries?"}
CheckValid --> |No| ReturnError["Return Validation Error"]
CheckValid --> |Yes| PrepareArgs["Prepare MCP Arguments"]
PrepareArgs --> CallMCP["Call McpClientService.callTool()"]
CallMCP --> ExecuteSearch["Execute via Laravel Boost Server"]
ExecuteSearch --> ProcessResults["Process Search Results"]
ProcessResults --> Deduplicate["Remove Duplicate Results"]
Deduplicate --> LimitResults["Limit to Top 10 Results"]
LimitResults --> ReturnResults["Return Structured Results"]
ReturnError --> End([End])
ReturnResults --> End
```

**Diagram sources**
- [SearchDocsTool.php:32-59](file://app/Ai/Tools/SearchDocsTool.php#L32-L59)

The tool accepts multiple search queries and packages, returning relevant documentation snippets with links to authoritative sources. Results are processed through the McpClientService and returned to the AI agent for formatting.

**Section sources**
- [SearchDocsTool.php:13-75](file://app/Ai/Tools/SearchDocsTool.php#L13-L75)

### Tinker Tool

The TinkerTool provides a safe execution environment for PHP code evaluation within the Laravel application context through the MCP protocol. It offers debugging capabilities and code testing functionality with comprehensive error handling via the Laravel Boost MCP server.

```mermaid
flowchart TD
CodeRequest["Code Execution Request"] --> ValidateCode["Validate Code Parameter"]
ValidateCode --> CheckEmpty{"Code Provided?"}
CheckEmpty --> |No| ReturnError["Return Validation Error"]
CheckEmpty --> |Yes| CleanCode["Clean Code Input"]
CleanCode --> StripTags["Strip PHP Opening Tags"]
StripTags --> SetTimeout["Set Execution Timeout"]
SetTimeout --> PrepareArgs["Prepare MCP Arguments"]
PrepareArgs --> CallMCP["Call McpClientService.callTool()"]
CallMCP --> ExecuteCode["Execute via Laravel Boost Server"]
ExecuteCode --> CaptureOutput["Capture Output Buffer"]
CaptureOutput --> GetResult["Get Execution Result"]
GetResult --> FormatResult["Format Execution Result"]
FormatResult --> ReturnResult["Return Structured Result"]
ReturnError --> End([End])
ReturnResult --> End
```

**Diagram sources**
- [TinkerTool.php:31-57](file://app/Ai/Tools/TinkerTool.php#L31-L57)

The tool removes PHP opening tags, validates timeout limits (maximum 60 seconds), captures output buffers, and returns both execution output and return values for comprehensive debugging support through the MCP protocol.

**Section sources**
- [TinkerTool.php:13-89](file://app/Ai/Tools/TinkerTool.php#L13-L89)

## MCP Client Service

### Service Architecture

The McpClientService provides a centralized interface for managing connections to the Laravel Boost MCP server via STDIO transport. It manages connection lifecycle, implements auto-reconnect logic, and provides comprehensive logging for all MCP operations.

```mermaid
classDiagram
class McpClientService {
+Client client
+bool initialized
+initialize() void
+callTool(toolName, arguments) string
+disconnect() void
+isConnected() bool
+getClient() Client
+terminate() void
-extractTextContent(result) string
}
class Client {
<<php-mcp/client>>
+initialize() void
+callTool(toolName, arguments) mixed
+isReady() bool
+disconnect() void
}
class ServerConfig {
+string name
+TransportType transport
+float timeout
+string command
+array args
+string workingDir
}
McpClientService --> Client
McpClientService --> ServerConfig
```

**Diagram sources**
- [McpClientService.php:20-279](file://app/Services/McpClientService.php#L20-L279)

The service creates a new client instance with STDIO transport, configures server parameters, and performs the MCP handshake. It manages connection state to prevent duplicate initialization and provides persistent connections for multiple tool calls.

**Section sources**
- [McpClientService.php:20-279](file://app/Services/McpClientService.php#L20-L279)

### Connection Management

The McpClientService implements sophisticated connection management with automatic initialization, health checks, and graceful shutdown capabilities. It handles connection failures with auto-reconnect logic and exponential backoff.

```mermaid
sequenceDiagram
participant App as Application
participant Service as McpClientService
participant Client as php-mcp Client
participant Server as Laravel Boost Server
App->>Service : callTool()
Service->>Service : initialize() if not ready
Service->>Client : initialize()
Client->>Server : MCP initialize
Server-->>Client : Handshake success
Client-->>Service : Ready
Service->>Client : callTool(tool, args)
Client->>Server : JSON-RPC 2.0 tools/call
Server-->>Client : Tool response
Client-->>Service : Response
Service->>Service : extractTextContent()
Service-->>App : Formatted result
```

**Diagram sources**
- [McpClientService.php:48-96](file://app/Services/McpClientService.php#L48-L96)
- [McpClientService.php:110-179](file://app/Services/McpClientService.php#L110-L179)

**Section sources**
- [McpClientService.php:48-179](file://app/Services/McpClientService.php#L48-L179)

### Configuration and Error Handling

The McpClientService reads configuration from the services.php configuration file and implements comprehensive error handling with logging and retry mechanisms. It validates configuration settings and handles connection failures gracefully.

```mermaid
flowchart TD
ConfigLoad["Load Configuration"] --> ValidateTimeout["Validate Timeout Setting"]
ValidateTimeout --> CheckRetries["Validate Max Retries"]
CheckRetries --> CheckDelay["Validate Retry Delay"]
CheckDelay --> InitService["Initialize Service"]
InitService --> HealthCheck["Health Check"]
HealthCheck --> ConnectionOK{"Connection OK?"}
ConnectionOK --> |Yes| Ready["Service Ready"]
ConnectionOK --> |No| Reconnect["Auto-Reconnect"]
Reconnect --> RetryAttempt["Retry Attempt"]
RetryAttempt --> MaxRetries{"Max Retries Reached?"}
MaxRetries --> |No| Backoff["Exponential Backoff"]
Backoff --> Reconnect
MaxRetries --> |Yes| ThrowError["Throw Exception"]
Ready --> Operation["Perform Operation"]
Operation --> Success["Operation Success"]
Success --> End([End])
ThrowError --> End
```

**Diagram sources**
- [services.php:38-43](file://config/services.php#L38-L43)
- [McpClientService.php:110-179](file://app/Services/McpClientService.php#L110-L179)

**Section sources**
- [services.php:38-43](file://config/services.php#L38-L43)
- [McpClientService.php:110-179](file://app/Services/McpClientService.php#L110-L179)

## Conversation Management

### Data Persistence Strategy

The conversation management system uses Laravel's Eloquent ORM to persist chat history with efficient querying and relationship management. The system maintains conversation metadata and message sequences for optimal AI context retrieval, with enhanced support for MCP tool interactions and project creation workflows.

```mermaid
erDiagram
CONVERSATIONS {
bigint id PK
bigint user_id
string title
timestamp created_at
timestamp updated_at
}
MESSAGES {
bigint id PK
bigint conversation_id FK
enum role
text content
timestamp created_at
timestamp updated_at
}
CONVERSATIONS ||--o{ MESSAGES : contains
```

**Diagram sources**
- [2026_04_02_123216_create_conversations_table.php:14-21](file://database/migrations/2026_04_02_123216_create_conversations_table.php#L14-L21)
- [2026_04_02_123238_create_messages_table.php:14-22](file://database/migrations/2026_04_02_123238_create_messages_table.php#L14-L22)

The conversation model includes helper methods for generating titles from initial messages and retrieving recent messages for AI context. The message model provides formatting capabilities using Markdown rendering and supports both user and assistant roles in the conversation history.

**Section sources**
- [Conversation.php:8-45](file://app/Models/Conversation.php#L8-L45)
- [Message.php:9-44](file://app/Models/Message.php#L9-L44)

### Message Processing Pipeline

The message processing pipeline handles both user and assistant messages with proper formatting and persistence, including enhanced support for MCP tool-generated responses and project creation workflow interactions. The system ensures message ordering and provides formatted content for display.

```mermaid
flowchart TD
UserMessage["User Message Input"] --> Validate["Validate Message"]
Validate --> CreateMessage["Create User Message Record"]
CreateMessage --> CheckTitle["Check Conversation Title"]
CheckTitle --> TitleExists{"Title Exists?"}
TitleExists --> |No| GenerateTitle["Generate Title from First Message"]
TitleExists --> |Yes| ProcessAI["Process AI Response with Tools"]
GenerateTitle --> ProcessAI
ProcessAI --> ExecuteTools["Execute MCP Tool Proxies"]
ExecuteTools --> ExecuteProjectTools["Execute Project Creation Tools"]
ExecuteProjectTools --> SaveAssistant["Save Assistant Response"]
SaveAssistant --> FormatContent["Format Content with Markdown"]
FormatContent --> ReturnResponse["Return Formatted Response"]
```

**Diagram sources**
- [ChatController.php:59-81](file://app/Http/Controllers/ChatController.php#L59-L81)
- [Message.php:39-42](file://app/Models/Message.php#L39-L42)

**Section sources**
- [ChatController.php:59-81](file://app/Http/Controllers/ChatController.php#L59-L81)
- [Message.php:39-42](file://app/Models/Message.php#L39-L42)

## User Interface

### Web Interface Design

The user interface provides an intuitive chat experience with responsive design and smooth interactions, enhanced with real-time MCP tool feedback, project creation workflow guidance, and improved conversation management capabilities.

```mermaid
graph LR
subgraph "UI Components"
Header[Header Bar]
Messages[Messages Container]
Input[Message Input]
Button[Send Button]
Loading[Loading Indicator]
ToolFeedback[Tool Feedback Panel]
ProjectWorkflow[Project Creation Workflow]
end
subgraph "JavaScript Features"
AutoScroll[Auto Scroll to Bottom]
AutoResize[Auto Resize Textarea]
AJAX[AJAX Form Submission]
ErrorHandling[Error Handling]
ToolIntegration[Tool Integration]
ProjectIntegration[Project Creation Integration]
end
Header --> Messages
Messages --> Input
Input --> Button
Button --> Loading
Messages -.-> AutoScroll
Input -.-> AutoResize
Button -.-> AJAX
AJAX -.-> ErrorHandling
ToolIntegration -.-> ToolFeedback
ProjectIntegration -.-> ProjectWorkflow
```

**Diagram sources**
- [chat.blade.php:10-391](file://resources/views/chat.blade.php#L10-L391)

The interface includes sophisticated JavaScript for enhanced user experience, including auto-scrolling to new messages, dynamic textarea resizing, comprehensive error handling, real-time MCP tool feedback, and integrated project creation workflow guidance. The design follows modern UI/UX principles with clear visual hierarchy and responsive behavior.

**Section sources**
- [chat.blade.php:10-391](file://resources/views/chat.blade.php#L10-L391)

### Responsive Design Implementation

The interface adapts seamlessly to different screen sizes and devices, ensuring accessibility across desktop, tablet, and mobile platforms. The design uses Tailwind CSS utility classes for consistent styling and responsive breakpoints, with enhanced support for MCP tool interaction indicators and project creation workflow visualization.

## AI Provider Configuration

### Multi-Provider Support

The system supports multiple AI providers through a unified configuration interface with enhanced MCP tool integration capabilities. This allows flexibility in choosing different AI services while maintaining consistent behavior across providers, including support for the expanded tool suite.

```mermaid
graph TB
subgraph "AI Configuration"
Default[Default Provider: z]
Providers[Provider Registry]
Anthropic[Anthropic]
Gemini[Gemini]
Azure[Azure OpenAI]
Ollama[Ollama]
MCP[MCP Tool Support]
Projects[Project Creation Tools]
end
subgraph "Environment Variables"
ZKey[Z_API_KEY]
ZURL[Z_URL]
AnthKey[ANTHROPIC_API_KEY]
GeminiKey[GEMINI_API_KEY]
GitHubToken[GITHUB_TOKEN]
</subgraph>
Default --> Providers
Providers --> Anthropic
Providers --> Gemini
Providers --> Azure
Providers --> Ollama
MCP --> Default
MCP --> Providers
Projects --> MCP
Projects --> GitHubToken
ZKey --> Default
ZURL --> Default
AnthKey --> Anthropic
GeminiKey --> Gemini
```

**Diagram sources**
- [ai.php:52-155](file://config/ai.php#L52-L155)

The configuration supports various AI providers including Anthropic, Gemini, Azure OpenAI, and local models through Ollama. The MCP tool integration works seamlessly across all providers, ensuring consistent tool execution regardless of the underlying AI service. The project creation tools integrate with all AI providers for comprehensive workflow orchestration.

**Section sources**
- [ai.php:52-155](file://config/ai.php#L52-L155)

### Provider Selection Logic

The system uses environment variables for provider configuration, allowing easy switching between different AI services. The default provider is set to 'z' which connects to a custom Anthropic endpoint, with MCP tool support available across all providers including the new project creation tools.

## Skills and Capabilities

### Domain-Specific Skills

The system includes specialized skills for different development domains, enhanced with comprehensive MCP tool integration for targeted assistance in specific areas of Laravel and PHP development, including new project creation capabilities.

```mermaid
graph TD
subgraph "Development Skills"
LaravelBest[Laravel Best Practices]
PestTesting[Pest Testing]
TailwindCSS[Tailwind CSS Development]
ProjectCreation[Project Creation]
MCPTools[MCP Tool Integration]
end
subgraph "Skill Categories"
Backend[Backend Development]
Testing[Test Automation]
Frontend[Frontend Development]
Tools[Tool Execution]
Projects[Project Management]
end
subgraph "Skill Activation"
Trigger[Skill Trigger Conditions]
Activation[Automatic Skill Activation]
Deactivation[Skill Deactivation]
</subgraph>
LaravelBest --> Backend
PestTesting --> Testing
TailwindCSS --> Frontend
ProjectCreation --> Projects
MCPTools --> Tools
Trigger --> Activation
Activation --> Deactivation
```

**Diagram sources**
- [AGENTS.md:24-31](file://AGENTS.md#L24-L31)
- [SKILL.md:1-66](file://.agents/skills/project-creation/SKILL.md#L1-L66)

The skills system includes automatic activation based on context, ensuring developers receive relevant assistance for their specific tasks. The MCP tool integration provides comprehensive development assistance including database operations, documentation search, code execution, and complete project creation workflows through secure external service communication.

**Section sources**
- [AGENTS.md:24-31](file://AGENTS.md#L24-L31)
- [SKILL.md:1-66](file://.agents/skills/project-creation/SKILL.md#L1-L66)

### Laravel Boost Integration

The system integrates with Laravel Boost for enhanced development capabilities, providing access to specialized tools and documentation search functionality. The MCP tool integration enhances this capability with direct database and code execution features, plus comprehensive project creation workflows through the Laravel Boost MCP server.

## Database Schema

### Conversation and Message Storage

The database schema is optimized for efficient conversation and message storage with appropriate indexing for common query patterns and enhanced support for MCP tool interactions and project creation workflows.

```mermaid
erDiagram
conversations {
bigint id PK
bigint user_id
string title
timestamp created_at
timestamp updated_at
}
messages {
bigint id PK
bigint conversation_id FK
enum role
text content
timestamp created_at
timestamp updated_at
}
conversations ||--o{ messages : has_many
messages }o--|| conversations : belongs_to
```

**Diagram sources**
- [2026_04_02_123216_create_conversations_table.php:14-21](file://database/migrations/2026_04_02_123216_create_conversations_table.php#L14-L21)
- [2026_04_02_123238_create_messages_table.php:14-22](file://database/migrations/2026_04_02_123238_create_messages_table.php#L14-L22)

The schema includes foreign key constraints for referential integrity and appropriate indexes for performance optimization. The conversation table includes timestamps for efficient sorting and filtering, supporting the enhanced conversation management capabilities including project creation workflow tracking.

**Section sources**
- [2026_04_02_123216_create_conversations_table.php:14-21](file://database/migrations/2026_04_02_123216_create_conversations_table.php#L14-L21)
- [2026_04_02_123238_create_messages_table.php:14-22](file://database/migrations/2026_04_02_123238_create_messages_table.php#L14-L22)

## API Endpoints

### Route Configuration

The system provides RESTful endpoints for chat functionality with clear URL patterns and HTTP method conventions, enhanced with MCP tool integration support and project creation workflow capabilities.

```mermaid
sequenceDiagram
participant Client as Client Application
participant Routes as Route Definitions
participant Controller as ChatController
participant Agent as DevBot Agent
participant ProjectTools as Project Creation Tools
Client->>Routes : GET /chat
Routes->>Controller : show()
Controller-->>Client : Render Chat Interface
Client->>Routes : POST /chat/message
Routes->>Controller : sendMessage()
Controller->>Agent : prompt(message)
Agent->>Agent : Execute MCP Tool Proxies if Requested
Agent->>ProjectTools : Execute Project Creation Workflow if Requested
Agent-->>Controller : AI Response with Tool Results
Controller-->>Client : JSON Response
```

**Diagram sources**
- [web.php:10-11](file://routes/web.php#L10-L11)

The routing system includes both web interface routes and API endpoints for programmatic access, with enhanced support for MCP tool interactions and project creation workflows. The design follows Laravel's conventional routing patterns for maintainability and predictability.

**Section sources**
- [web.php:10-11](file://routes/web.php#L10-L11)

## Error Handling

### Comprehensive Error Management

The system implements robust error handling across all layers, providing meaningful feedback to users while maintaining system stability, with enhanced error handling for MCP tool operations and project creation workflows.

```mermaid
flowchart TD
Request[Incoming Request] --> Validate[Validate Input]
Validate --> ValidRequest{Valid Request?}
ValidRequest --> |No| ValidationError[Return Validation Error]
ValidRequest --> |Yes| ProcessRequest[Process Request]
ProcessRequest --> ExecuteTools["Execute MCP Tool Proxies"]
ExecuteTools --> ExecuteProjectTools["Execute Project Creation Tools"]
ExecuteProjectTools --> ToolSuccess{Tool Execution Success?}
ToolSuccess --> |Yes| ProcessAI[Process AI Response]
ToolSuccess --> |No| ToolError[Handle Tool Error]
ToolError --> LogToolError[Log Tool Error Details]
LogToolError --> ReturnToolError[Return Tool Error Response]
ProcessAI --> AIResponse[Generate AI Response]
AIResponse --> Success{Operation Success?}
Success --> |Yes| ReturnSuccess[Return Success Response]
Success --> |No| HandleError[Handle Error]
HandleError --> LogError[Log Error Details]
LogError --> ReturnError[Return Error Response]
ValidationError --> End([End])
ReturnSuccess --> End
ReturnError --> End
ReturnToolError --> End
```

**Diagram sources**
- [ChatController.php:93-110](file://app/Http/Controllers/ChatController.php#L93-L110)

The error handling system includes detailed logging, user-friendly error messages, graceful degradation when AI services are unavailable, comprehensive error handling for MCP tool operations, and specific error handling for project creation workflows. This ensures users receive helpful feedback even when technical issues occur.

**Section sources**
- [ChatController.php:93-110](file://app/Http/Controllers/ChatController.php#L93-L110)

## Security Considerations

### MCP Tool Security

The MCP tool integration implements comprehensive security measures to protect the application from malicious tool usage while providing necessary development capabilities. The McpClientService acts as a security boundary between the AI agent and external services, with enhanced security for project creation tools.

```mermaid
flowchart TD
ToolRequest[Tool Request] --> ValidateRequest["Validate Tool Request"]
ValidateRequest --> CheckPermissions["Check Permissions"]
CheckPermissions --> PermissionGranted{"Permission Granted?"}
PermissionGranted --> |No| DenyAccess["Deny Access with Error"]
PermissionGranted --> |Yes| ExecuteTool["Execute Tool via McpClientService"]
ExecuteTool --> ApplySecurity["Apply Security Restrictions"]
ApplySecurity --> CheckLimits["Check Resource Limits"]
CheckLimits --> LimitsOK{"Within Limits?"}
LimitsOK --> |No| BlockExecution["Block Execution"]
LimitsOK --> |Yes| LogExecution["Log Tool Execution"]
LogExecution --> ReturnResult["Return Safe Result"]
BlockExecution --> LogBlock["Log Security Block"]
LogBlock --> ReturnBlocked["Return Blocked Response"]
DenyAccess --> End([End])
ReturnResult --> End
ReturnBlocked --> End
```

**Diagram sources**
- [DatabaseQueryTool.php:31-49](file://app/Ai/Tools/DatabaseQueryTool.php#L31-L49)
- [TinkerTool.php:35-40](file://app/Ai/Tools/TinkerTool.php#L35-L40)
- [FileSystemTool.php:238-280](file://app/Ai/Tools/FileSystemTool.php#L238-L280)
- [GitTool.php:294-304](file://app/Ai/Tools/GitTool.php#L294-L304)
- [GitHubTool.php:39-41](file://app/Ai/Tools/GitHubTool.php#L39-L41)
- [McpClientService.php:140-179](file://app/Services/McpClientService.php#L140-L179)

The security model includes read-only database access restrictions, code execution timeouts, output capture and sanitization, path traversal prevention for file operations, Git command validation, GitHub API authentication, and comprehensive logging for all tool operations. The McpClientService provides a centralized security boundary that validates all tool requests before forwarding them to the Laravel Boost MCP server.

**Section sources**
- [DatabaseQueryTool.php:31-74](file://app/Ai/Tools/DatabaseQueryTool.php#L31-L74)
- [TinkerTool.php:35-60](file://app/Ai/Tools/TinkerTool.php#L35-L60)
- [FileSystemTool.php:238-280](file://app/Ai/Tools/FileSystemTool.php#L238-L280)
- [GitTool.php:294-304](file://app/Ai/Tools/GitTool.php#L294-L304)
- [GitHubTool.php:39-41](file://app/Ai/Tools/GitHubTool.php#L39-L41)
- [McpClientService.php:140-179](file://app/Services/McpClientService.php#L140-L179)

### Project Creation Security

The project creation tools implement additional security measures to protect the application from malicious project creation attempts while providing necessary development capabilities. All project operations are scoped to the `storage/projects/` directory with comprehensive path validation and access controls.

**Section sources**
- [FileSystemTool.php:238-280](file://app/Ai/Tools/FileSystemTool.php#L238-L280)
- [GitTool.php:294-304](file://app/Ai/Tools/GitTool.php#L294-L304)
- [GitHubTool.php:39-41](file://app/Ai/Tools/GitHubTool.php#L39-L41)

## Performance Considerations

### Optimization Strategies

The system incorporates several performance optimization strategies to ensure responsive interactions and efficient resource utilization, with enhanced considerations for MCP tool execution and project creation workflows.

**Database Performance**
- Proper indexing on conversation_id and created_at fields
- Efficient query patterns for message retrieval
- Limited message history for AI context (50 messages)
- MCP tool result caching where appropriate

**Memory Management**
- Lazy loading of conversation messages
- Efficient model hydration
- Proper garbage collection
- Tool execution memory limits

**Network Optimization**
- Asynchronous AI API calls
- Response caching where appropriate
- Efficient JSON serialization
- MCP tool batch processing

**Tool Execution Optimization**
- Timeout limits for MCP tool operations
- Result size limiting for database queries
- Output buffering for code execution
- Connection pooling for database operations
- Persistent MCP client connections to reduce startup overhead
- Project directory caching for file system operations
- Git repository caching for version control operations
- GitHub API response caching for repository operations

**Project Creation Optimization**
- Concurrent tool execution for project creation steps
- Batch file operations for OpenSpec artifact creation
- Optimized Git operations with staged commits
- Efficient GitHub API calls with rate limit handling

## Deployment and Setup

### Installation Requirements

The system requires specific PHP and Laravel versions along with supporting packages for full functionality, including MCP tool integration capabilities and expanded project creation tools.

**System Requirements**
- PHP 8.3 or higher
- Laravel Framework 13.x
- Laravel AI 0.x
- Laravel Boost 2.x (for enhanced features)
- Composer for dependency management
- php-mcp/client library for MCP protocol communication
- Git for project version control
- GitHub CLI or API access for repository management

**Installation Process**
1. Install dependencies via Composer
2. Configure environment variables for AI providers, MCP tools, and GitHub integration
3. Run database migrations
4. Build frontend assets
5. Configure GitHub token for repository access
6. Start development server

**Section sources**
- [composer.json:11-16](file://composer.json#L11-L16)
- [composer.json:41-75](file://composer.json#L41-L75)

### Environment Configuration

The system uses environment variables for flexible deployment across different environments with sensible defaults for local development, including MCP client configuration, AI provider settings, and project creation configuration.

**MCP Client Configuration**
- MCP_CLIENT_COMMAND: Artisan command to run (default: `php artisan boost:mcp`)
- MCP_CLIENT_TIMEOUT: Maximum seconds to wait for tool response (default: 60)
- MCP_CLIENT_MAX_RETRIES: Number of reconnect attempts (default: 3)
- MCP_CLIENT_RETRY_DELAY: Base delay between retries in milliseconds (default: 1000)

**Project Configuration**
- GITHUB_TOKEN: GitHub personal access token for repository creation
- PROJECT_DEFAULT_BRANCH: Default Git branch name (default: 'main')
- AI_PROJECTS_BASE_PATH: Base path for project storage (default: `storage/projects/`)

**Section sources**
- [services.php:38-43](file://config/services.php#L38-L43)
- [ai.php:52-56](file://config/ai.php#L52-L56)

## Conclusion

DevBot represents a comprehensive AI-powered development assistant built on Laravel's robust framework with extensive MCP tool integration and advanced project creation capabilities. The system successfully combines modern AI capabilities with enterprise-grade architecture, providing developers with an intuitive platform for getting help with Laravel and PHP development challenges while offering powerful tool execution capabilities and complete micro-SaaS project orchestration through secure MCP protocol communication.

Key strengths of the system include its modular architecture with comprehensive MCP tool proxy integration, robust error handling with enhanced tool security, responsive user interface with real-time tool feedback, flexible AI provider configuration that supports multiple MCP tool implementations, and comprehensive project creation workflow orchestration. The integration of eight specialized MCP-powered tools (DatabaseQueryTool, DatabaseSchemaTool, SearchDocsTool, TinkerTool, FileSystemTool, GitTool, GitHubTool, and OpenSpecTool) provides comprehensive development assistance capabilities through secure external service communication.

The system's design emphasizes maintainability, scalability, security, and user experience, making it suitable for both individual developers and development teams. The MCP tool integration ensures that developers can directly interact with their application's database, search documentation, execute code, create projects, manage version control, and integrate with GitHub through the Laravel Boost MCP server.

The addition of project creation capabilities transforms DevBot from a development assistant into a complete development platform, enabling developers to move from idea to implementation with minimal friction. The Project Creation skill provides structured guidance for requirements gathering, project setup, specification creation, version control initialization, and remote repository management.

Future enhancements could include additional MCP tools for deployment automation, expanded AI provider support, advanced project management features, enhanced tool execution capabilities, and integration with CI/CD pipelines. The comprehensive foundation established by the current implementation provides an excellent base for continued evolution and improvement of the DevBot system.