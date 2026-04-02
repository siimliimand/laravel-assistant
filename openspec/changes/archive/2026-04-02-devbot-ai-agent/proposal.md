## Why

Developers need an AI-powered assistant integrated directly into the Laravel application to help answer development-related questions, provide code guidance, and assist with problem-solving. Currently, there's no in-app development assistant, forcing developers to switch context to external tools. DevBot will provide immediate, contextual development assistance within the application itself.

## What Changes

- Create a DevBot AI agent configured for development-related conversations
- Build a visual chat interface for real-time conversations with DevBot
- Implement conversation history persistence to maintain chat context
- Add a route and controller to handle chat interactions
- Create database migrations for storing chat conversations and messages
- Style the chat UI with Tailwind CSS for a modern, responsive design

## Capabilities

### New Capabilities

- `devbot-agent`: AI agent implementation for development assistance, including agent configuration, prompt engineering, and tool integration
- `chat-interface`: Visual chat UI component with real-time messaging, conversation history, and responsive design
- `conversation-storage`: Database schema and models for persisting chat conversations and messages

### Modified Capabilities
<!-- Existing capabilities whose REQUIREMENTS are changing (not just implementation).
     Only list here if spec-level behavior changes. Each needs a delta spec file.
     Use existing spec names from openspec/specs/. Leave empty if no requirement changes. -->

## Impact

- **New database tables**: `conversations` and `messages` for storing chat history
- **New routes**: Chat interface route and API endpoint for sending messages
- **New views**: Blade view for the chat UI with Tailwind CSS styling
- **New agent class**: `App\Ai\Agents\DevBot` implementing the Laravel AI SDK Agent contract
- **New controller**: `ChatController` to handle chat interactions
- **Dependencies**: Uses existing `laravel/ai` package (already configured with Anthropic as default provider)
