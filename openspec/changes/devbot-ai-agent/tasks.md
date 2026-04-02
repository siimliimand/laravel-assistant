# Tasks

## 1. Database Migrations and Models

- [x] 1.1 Create migration for conversations table with id, user_id (nullable), title, timestamps
- [x] 1.2 Create migration for messages table with id, conversation_id (foreign key), role (enum), content (text), timestamps
- [x] 1.3 Run migrations to create tables
- [x] 1.4 Create Conversation model with hasMany messages relationship and fillable fields
- [x] 1.5 Create Message model with belongsTo conversation relationship and fillable fields
- [x] 1.6 Add method to Conversation model to generate title from first message
- [x] 1.7 Add method to Conversation model to retrieve messages limited to 50 with proper ordering

## 2. DevBot AI Agent

- [x] 2.1 Create DevBot agent class using `php artisan make:agent DevBot`
- [x] 2.2 Configure DevBot with PHP attributes (Provider, Model, MaxSteps, Temperature)
- [x] 2.3 Implement instructions() method with development-focused system prompt
- [x] 2.4 Implement messages() method to retrieve conversation history
- [x] 2.5 Test DevBot agent instantiation and basic prompt response

## 3. Routes and Controller

- [x] 3.1 Add GET route `/chat` to display chat interface
- [x] 3.2 Add POST route `/chat/message` to handle message submission
- [x] 3.3 Create ChatController with show() method for chat page
- [x] 3.4 Implement sendMessage() method in ChatController to process messages
- [x] 3.5 Add message validation (required, non-empty string)
- [x] 3.6 Implement conversation creation logic when no active conversation exists
- [x] 3.7 Implement message saving for both user and assistant roles
- [x] 3.8 Add error handling for API failures with user-friendly messages

## 4. Chat Interface View

- [ ] 4.1 Create Blade view file for chat interface at resources/views/chat.blade.php
- [ ] 4.2 Build chat layout container with Tailwind CSS classes
- [ ] 4.3 Create message display section with scrollable history area
- [ ] 4.4 Implement user message bubble styling (right-aligned, blue background)
- [ ] 4.5 Implement assistant message bubble styling (left-aligned, gray background)
- [ ] 4.6 Add message input form with textarea and submit button
- [ ] 4.7 Display timestamps for each message
- [ ] 4.8 Add welcome message for empty conversations
- [ ] 4.9 Implement loading indicator while waiting for AI response

## 5. JavaScript Enhancement

- [ ] 5.1 Add JavaScript for AJAX form submission
- [ ] 5.2 Implement loading state toggle during message send
- [ ] 5.3 Add auto-scroll to bottom when new messages appear
- [ ] 5.4 Implement client-side validation for empty messages
- [ ] 5.5 Add error message display for failed requests
- [ ] 5.6 Disable input field while waiting for response

## 6. Markdown and Code Formatting

- [ ] 6.1 Install and configure a markdown parsing library (e.g., parsedown or similar)
- [ ] 6.2 Create Blade component or helper for rendering markdown
- [ ] 6.3 Implement code block rendering with syntax highlighting
- [ ] 6.4 Add CSS styles for code blocks (scrollable, monospace font)
- [ ] 6.5 Style inline code with distinct background and monospace font
- [ ] 6.6 Test markdown rendering with various code examples

## 7. Responsive Design and Styling

- [ ] 7.1 Ensure chat container is responsive on mobile (< 768px)
- [ ] 7.2 Ensure chat container has max-width on desktop (>= 768px)
- [ ] 7.3 Fix message input at bottom of viewport
- [ ] 7.4 Make message history area scrollable
- [ ] 7.5 Ensure touch targets are at least 44px tall on mobile
- [ ] 7.6 Test on various screen sizes and adjust as needed
- [ ] 7.7 Run `npm run build` to compile Tailwind CSS assets

## 8. Testing and Validation

- [ ] 8.1 Test full conversation flow: send message → receive response → display
- [ ] 8.2 Test conversation persistence across page reloads
- [ ] 8.3 Test empty message validation
- [ ] 8.4 Test error handling when API is unavailable
- [ ] 8.5 Test code block rendering in messages
- [ ] 8.6 Test responsive design on mobile and desktop
- [ ] 8.7 Verify database records are created correctly
- [ ] 8.8 Run `vendor/bin/pint --dirty --format agent` to format PHP code
