# DevBot - Laravel AI Development Assistant

DevBot is an AI-powered development assistant built with Laravel. It provides an interactive chat interface for developers to get help with programming questions, code review, debugging, architecture decisions, and best practices.

## Features

- **Interactive Chat Interface** - Modern, responsive UI with real-time messaging
- **Conversation Management** - Create, switch, and search through conversation history
- **AI-Powered Responses** - Powered by Laravel AI with support for multiple providers (Anthropic, OpenAI, Gemini, etc.)
- **Project Creation** - Transform micro-SaaS ideas into structured projects with GitHub integration
- **MCP Tool Integration** - Connects to Laravel Boost MCP server for enhanced capabilities:
  - **Database Query Tool** - Execute read-only SQL queries safely
  - **Database Schema Tool** - Inspect table structure and relationships
  - **Search Docs Tool** - Search Laravel and package documentation
  - **Tinker Tool** - Execute PHP code in application context
- **Project Management Tools** - Built-in tools for project creation:
  - **FileSystem Tool** - Secure file operations within project directories
  - **Git Tool** - Initialize, commit, and push to Git repositories
  - **GitHub Tool** - Create GitHub repositories via API
  - **OpenSpec Tool** - Manage project specifications and workflow
- **Markdown Rendering** - Rich formatting for code blocks and technical content
- **Mobile Responsive** - Works seamlessly on desktop and mobile devices
- **Conversation Persistence** - All conversations stored in SQLite database

## Tech Stack

| Category     | Technology                         |
| ------------ | ---------------------------------- |
| **Backend**  | Laravel 13, PHP 8.3                |
| **AI**       | Laravel AI v0.4+, Laravel Boost v2 |
| **Frontend** | Tailwind CSS v4, Vite 8            |
| **Database** | SQLite (default), configurable     |
| **Testing**  | Pest v4                            |
| **MCP**      | php-mcp/client v1.0                |

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+ & NPM
- SQLite (default) or other supported database

## Installation

### Quick Start

```bash
# Clone the repository
git clone <repository-url> laravel-assistant
cd laravel-assistant

# Run setup (installs dependencies, generates key, migrates database, builds assets)
composer run setup
```

### Manual Installation

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate

# Build frontend assets
npm run build
```

## Configuration

### AI Provider

Configure your AI provider in `.env`:

```env
# Default AI provider (uses Z.ai proxy by default)
Z_API_KEY=your-api-key
Z_URL=https://api.z.ai/api/anthropic/v1

# Or use Anthropic directly
ANTHROPIC_API_KEY=your-api-key

# Or OpenAI
OPENAI_API_KEY=your-api-key
```

### DevBot Model

Customize the AI model used by DevBot:

```env
DEVBOT_MODEL=claude-haiku-4-5-20251001
```

### GitHub Integration (Optional)

Enable project creation with GitHub repository support:

```env
GITHUB_TOKEN=your-github-personal-access-token
PROJECT_DEFAULT_BRANCH=main
```

The GitHub token needs `repo` scope. Create one at https://github.com/settings/tokens.

### MCP Client (Optional)

Override default MCP client settings:

```env
MCP_CLIENT_COMMAND=php artisan boost:mcp
MCP_CLIENT_TIMEOUT=60
MCP_CLIENT_MAX_RETRIES=3
MCP_CLIENT_RETRY_DELAY=1000
```

## Usage

### Development Server

Start all development services (server, queue, logs, Vite):

```bash
composer run dev
```

Or start services individually:

```bash
# Laravel development server
php artisan serve

# Frontend hot reload
npm run dev
```

### Accessing the Application

1. Navigate to `http://localhost:8000`
2. Click "New Chat" to start a conversation
3. Ask questions about Laravel, PHP, or development topics

### Available Routes

| Route            | Method | Description                          |
| ---------------- | ------ | ------------------------------------ |
| `/`              | GET    | Welcome page                         |
| `/chat`          | GET    | Chat interface (latest conversation) |
| `/chat/{id}`     | GET    | Specific conversation                |
| `/chat/new`      | POST   | Create new conversation              |
| `/chat/message`  | POST   | Send message to DevBot               |
| `/api/chat/{id}` | GET    | Get conversation JSON                |

## Project Structure

``` plaintext
app/
├── Ai/
│   ├── Agents/
│   │   └── DevBot.php          # AI agent implementation
│   └── Tools/
│       ├── DatabaseQueryTool.php    # Read-only SQL queries
│       ├── DatabaseSchemaTool.php   # Schema inspection
│       ├── FileSystemTool.php       # Project file operations
│       ├── GitHubTool.php           # GitHub API integration
│       ├── GitTool.php              # Git repository management
│       ├── OpenSpecTool.php         # OpenSpec workflow assistance
│       ├── SearchDocsTool.php       # Documentation search
│       └── TinkerTool.php           # PHP code execution
├── Helpers/
│   └── Markdown.php            # Markdown rendering helper
├── Http/Controllers/
│   └── ChatController.php      # Chat endpoints
├── Models/
│   ├── Conversation.php        # Conversation model
│   └── Message.php             # Message model
└── Services/
    └── McpClientService.php    # MCP client management

resources/views/
└── chat.blade.php              # Main chat interface

.agents/skills/
├── laravel-best-practices/     # Laravel coding standards
├── pest-testing/               # Pest testing guidelines
├── project-creation/           # Project creation workflow guide
└── tailwindcss-development/    # Tailwind CSS patterns

storage/projects/               # Created projects directory (gitignored)
```

## Development

### Code Style

Format PHP code using Laravel Pint:

```bash
vendor/bin/pint --dirty
```

### Testing

Run the test suite:

```bash
composer run test
# or
php artisan test --compact
```

Run specific tests:

```bash
php artisan test --filter=ChatTest
```

### Artisan Commands

```bash
# List available commands
php artisan list

# View routes
php artisan route:list

# Check configuration
php artisan config:show ai.providers
```

## Architecture

### DevBot Agent

The DevBot agent implements Laravel AI's agent contracts:

- **Agent** - Core agent functionality
- **Conversational** - Maintains conversation context
- **HasTools** - Provides access to MCP tools

The agent uses a system prompt that focuses on development topics, Laravel best practices, and helpful code examples.

### MCP Integration

The `McpClientService` manages connections to the Laravel Boost MCP server:

- Auto-reconnect with exponential backoff
- Comprehensive logging
- Graceful shutdown handling

### Conversation Flow

1. User sends message via `/chat/message`
2. Message stored in database
3. DevBot receives conversation history (last 50 messages)
4. Agent may call MCP tools for database/docs access
5. Response stored and returned to user

### Project Creation Workflow

DevBot can create complete micro-SaaS projects from user ideas:

1. **Gather Requirements** - Ask clarifying questions about features and goals
2. **Create Project Directory** - Secure directory in `storage/projects/`
3. **Generate OpenSpec Artifacts** - Create proposal, design, specs, and tasks
4. **Initialize Git** - Set up version control with initial commit
5. **Create GitHub Repo** - Create remote repository via GitHub API
6. **Push to GitHub** - Push project to remote for collaboration

All file operations are scoped to `storage/projects/` for security.
Path traversal attacks are blocked and logged.

## AI Providers Supported

| Provider      | Driver       | Environment Variable   |
| ------------- | ------------ | ---------------------- |
| Anthropic     | `anthropic`  | `ANTHROPIC_API_KEY`    |
| OpenAI        | `openai`     | `OPENAI_API_KEY`       |
| Google Gemini | `gemini`     | `GEMINI_API_KEY`       |
| Azure OpenAI  | `azure`      | `AZURE_OPENAI_API_KEY` |
| Groq          | `groq`       | `GROQ_API_KEY`         |
| Mistral       | `mistral`    | `MISTRAL_API_KEY`      |
| DeepSeek      | `deepseek`   | `DEEPSEEK_API_KEY`     |
| Ollama        | `ollama`     | `OLLAMA_BASE_URL`      |
| OpenRouter    | `openrouter` | `OPENROUTER_API_KEY`   |
| X.AI          | `xai`        | `XAI_API_KEY`          |

## Skills

This project includes pre-configured AI skills for enhanced development:

- **Laravel Best Practices** - Database performance, security, caching, Eloquent patterns, and more
- **Pest Testing** - Testing patterns, assertions, mocking, and browser testing
- **Project Creation** - Complete workflow for creating micro-SaaS projects from idea to GitHub repository
- **Tailwind CSS Development** - Responsive layouts, components, and styling patterns

## Troubleshooting

### Vite Asset Error

If you see "Unable to locate file in Vite manifest":

```bash
npm run build
# or start dev server
npm run dev
```

### MCP Connection Issues

Check the logs for MCP client errors:

```bash
php artisan pail
```

### Database Issues

Reset the database:

```bash
php artisan migrate:fresh
```

## Contributing

Contributions are welcome! Please ensure:

1. Code follows Laravel Pint formatting
2. All tests pass
3. New features include tests

## Security

If you discover a security vulnerability, please email the maintainers directly rather than opening a public issue.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
