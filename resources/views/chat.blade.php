<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DevBot - Development Assistant</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 h-screen flex flex-col overflow-hidden">
    <!-- Chat Container -->
    <div class="flex-1 flex flex-col w-full max-w-4xl mx-auto h-full bg-white shadow-lg sm:my-4 sm:rounded-lg overflow-hidden">
        
        <!-- Header -->
        <header class="bg-linear-to-r from-blue-600 to-blue-700 text-white px-4 md:px-6 py-4 shadow-md shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 rounded-full p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">DevBot</h1>
                        <p class="text-xs text-blue-100">Development Assistant</p>
                    </div>
                </div>
                @if($conversation)
                <div class="text-right hidden sm:block">
                    <p class="text-xs text-blue-100">Conversation</p>
                    <p class="text-sm font-semibold truncate max-w-xs">{{ $conversation->title ?? 'New Chat' }}</p>
                </div>
                @endif
            </div>
        </header>

        <!-- Error Message -->
        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mx-4 mt-4 rounded">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-red-700 text-sm">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <!-- Messages Area -->
        <div id="messages-container" class="flex-1 overflow-y-auto px-4 md:px-6 py-6 space-y-4 scroll-smooth">
            @if($messages->isEmpty())
                <!-- Welcome Message -->
                <div class="flex items-start space-x-3">
                    <div class="shrink-0 w-8 h-8 rounded-full bg-linear-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                        AI
                    </div>
                    <div class="flex-1 bg-gray-100 rounded-2xl rounded-tl-md px-4 py-3 max-w-3xl">
                        <div class="flex items-baseline space-x-2 mb-1">
                            <span class="font-semibold text-sm text-gray-900">DevBot</span>
                            <span class="text-xs text-gray-500">{{ now()->format('g:i A') }}</span>
                        </div>
                        <div class="text-gray-700 text-sm leading-relaxed">
                            <p class="mb-2">👋 Hi! I'm <strong>DevBot</strong>, your development assistant.</p>
                            <p class="mb-2">I can help you with:</p>
                            <ul class="list-disc list-inside space-y-1 ml-2 mb-2">
                                <li>Laravel & PHP development</li>
                                <li>Code architecture & best practices</li>
                                <li>Debugging & problem solving</li>
                                <li>Database design & Eloquent queries</li>
                                <li>Frontend development (JavaScript, Tailwind CSS)</li>
                            </ul>
                            <p>What would you like help with today?</p>
                        </div>
                    </div>
                </div>
            @else
                <!-- Message History -->
                @foreach($messages as $message)
                    @if($message->role === 'user')
                        <!-- User Message -->
                        <div class="flex items-start space-x-3 flex-row-reverse space-x-reverse">
                            <div class="shrink-0 w-8 h-8 rounded-full bg-linear-to-br from-gray-600 to-gray-700 flex items-center justify-center text-white font-bold text-sm">
                                U
                            </div>
                            <div class="flex-1 bg-blue-600 text-white rounded-2xl rounded-tr-md px-4 py-3 max-w-3xl">
                                <div class="flex items-baseline space-x-2 mb-1 justify-end">
                                    <span class="text-xs text-blue-100">{{ $message->created_at->format('g:i A') }}</span>
                                    <span class="font-semibold text-sm text-blue-50">You</span>
                                </div>
                                <div class="text-sm leading-relaxed prose prose-invert max-w-none">
                                    {!! nl2br(e($message->content)) !!}
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Assistant Message -->
                        <div class="flex items-start space-x-3">
                            <div class="shrink-0 w-8 h-8 rounded-full bg-linear-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                                AI
                            </div>
                            <div class="flex-1 bg-gray-100 rounded-2xl rounded-tl-md px-4 py-3 max-w-3xl">
                                <div class="flex items-baseline space-x-2 mb-1">
                                    <span class="font-semibold text-sm text-gray-900">DevBot</span>
                                    <span class="text-xs text-gray-500">{{ $message->created_at->format('g:i A') }}</span>
                                </div>
                                <div class="text-gray-700 text-sm leading-relaxed markdown-content">
                                    {!! $message->formattedContent() !!}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

            <!-- Loading Indicator (hidden by default) -->
            <div id="loading-indicator" class="hidden flex items-start space-x-3">
                <div class="shrink-0 w-8 h-8 rounded-full bg-linear-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                    AI
                </div>
                <div class="flex-1 bg-gray-100 rounded-2xl rounded-tl-md px-4 py-3 max-w-3xl">
                    <div class="flex items-center space-x-2">
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
                        </div>
                        <span class="text-sm text-gray-600 ml-2">DevBot is typing...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Input Form -->
        <div class="shrink-0 border-t border-gray-200 bg-white px-4 md:px-6 py-4 sticky bottom-0 z-10">
            <form id="chat-form" action="{{ route('chat.message') }}" method="POST" class="space-y-3">
                @csrf
                @if($conversation)
                <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
                @endif
                
                <div class="flex items-end space-x-3">
                    <div class="flex-1">
                        <textarea 
                            id="message-input"
                            name="message" 
                            rows="1"
                            placeholder="Ask me anything about development..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none text-sm"
                            style="min-height: 44px; max-height: 150px;"
                            required
                        ></textarea>
                    </div>
                    <button 
                        type="submit" 
                        id="send-button"
                        class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        style="min-height: 44px;"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <span>Send</span>
                    </button>
                </div>
            </form>
            <p class="text-xs text-gray-500 mt-2 text-center">
                DevBot is an AI assistant. Responses may not always be accurate.
            </p>
        </div>
    </div>

    <!-- JavaScript for Enhanced UX -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');
            const sendButton = document.getElementById('send-button');
            const loadingIndicator = document.getElementById('loading-indicator');
            const messagesContainer = document.getElementById('messages-container');
            const chatForm = document.getElementById('chat-form');

            // Auto-scroll to bottom on page load
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            // Auto-resize textarea
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 150) + 'px';
            });

            // Show error message function
            function showError(message) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'bg-red-50 border-l-4 border-red-500 p-4 mx-4 mt-4 rounded';
                errorDiv.innerHTML = `
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-red-700 text-sm">${message}</p>
                    </div>
                `;
                
                // Insert error before messages container
                const header = document.querySelector('header');
                header.insertAdjacentElement('afterend', errorDiv);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    errorDiv.remove();
                }, 5000);
            }

            // Reset form state function
            function resetFormState() {
                sendButton.disabled = false;
                messageInput.disabled = false;
                messageInput.focus();
                loadingIndicator.classList.add('hidden');
            }

            // Scroll to bottom function
            function scrollToBottom() {
                messagesContainer.scrollTo({
                    top: messagesContainer.scrollHeight,
                    behavior: 'smooth'
                });
            }

            // Append new message to chat
            function appendMessage(role, content, timestamp) {
                const messageWrapper = document.createElement('div');
                
                if (role === 'user') {
                    messageWrapper.className = 'flex items-start space-x-3 flex-row-reverse space-x-reverse';
                    messageWrapper.innerHTML = `
                        <div class="shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-gray-600 to-gray-700 flex items-center justify-center text-white font-bold text-sm">
                            U
                        </div>
                        <div class="flex-1 bg-blue-600 text-white rounded-2xl rounded-tr-md px-4 py-3 max-w-3xl">
                            <div class="flex items-baseline space-x-2 mb-1 justify-end">
                                <span class="text-xs text-blue-100">${timestamp}</span>
                                <span class="font-semibold text-sm text-blue-50">You</span>
                            </div>
                            <div class="text-sm leading-relaxed prose prose-invert max-w-none">
                                ${content.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    `;
                } else {
                    messageWrapper.className = 'flex items-start space-x-3';
                    messageWrapper.innerHTML = `
                        <div class="shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                            AI
                        </div>
                        <div class="flex-1 bg-gray-100 rounded-2xl rounded-tl-md px-4 py-3 max-w-3xl">
                            <div class="flex items-baseline space-x-2 mb-1">
                                <span class="font-semibold text-sm text-gray-900">DevBot</span>
                                <span class="text-xs text-gray-500">${timestamp}</span>
                            </div>
                            <div class="text-gray-700 text-sm leading-relaxed markdown-content">
                                ${content}
                            </div>
                        </div>
                    `;
                }
                
                // Insert before loading indicator
                loadingIndicator.insertAdjacentElement('beforebegin', messageWrapper);
            }

            // Handle form submission with AJAX
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const message = messageInput.value.trim();
                
                // Client-side validation for empty messages
                if (!message) {
                    showError('Please enter a message');
                    messageInput.focus();
                    return;
                }

                // Disable input field and button while waiting for response
                sendButton.disabled = true;
                messageInput.disabled = true;
                
                // Show loading indicator
                loadingIndicator.classList.remove('hidden');
                
                // Auto-scroll to bottom to show loading indicator
                scrollToBottom();

                try {
                    // Prepare form data with explicit fields
                    const formData = new FormData();
                    formData.append('_token', document.querySelector('input[name="_token"]').value);
                    formData.append('message', message);
                    
                    if (document.querySelector('input[name="conversation_id"]')) {
                        formData.append('conversation_id', document.querySelector('input[name="conversation_id"]').value);
                    }
                    
                    // Send AJAX request
                    const response = await fetch(chatForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        // Handle validation errors
                        if (response.status === 422) {
                            const errorData = await response.json();
                            const errorMessage = errorData.errors.message ? errorData.errors.message[0] : errorData.message;
                            throw new Error(errorMessage || 'Validation failed');
                        }
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        // Clear input
                        messageInput.value = '';
                        messageInput.style.height = 'auto';
                        
                        // Append user message
                        const userTimestamp = new Date().toLocaleTimeString('en-US', { 
                            hour: 'numeric', 
                            minute: '2-digit',
                            hour12: true 
                        });
                        appendMessage('user', message.replace(/</g, '&lt;').replace(/>/g, '&gt;'), userTimestamp);
                        
                        // Append assistant message
                        const assistantTimestamp = new Date().toLocaleTimeString('en-US', { 
                            hour: 'numeric', 
                            minute: '2-digit',
                            hour12: true 
                        });
                        appendMessage('assistant', data.response, assistantTimestamp);
                        
                        // Update conversation ID if this was a new conversation
                        if (data.conversation_id) {
                            const hiddenInput = chatForm.querySelector('input[name="conversation_id"]');
                            if (hiddenInput) {
                                hiddenInput.value = data.conversation_id;
                            } else {
                                const newInput = document.createElement('input');
                                newInput.type = 'hidden';
                                newInput.name = 'conversation_id';
                                newInput.value = data.conversation_id;
                                chatForm.appendChild(newInput);
                            }
                        }
                        
                        // Reset form state
                        resetFormState();
                        
                        // Scroll to show new messages
                        scrollToBottom();
                    } else {
                        throw new Error(data.message || 'Failed to send message');
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                    
                    // Show error message
                    showError(error.message || 'Failed to send message. Please try again.');
                    
                    // Reset form state
                    resetFormState();
                }
            });

            // Enter key to submit (Shift+Enter for new line)
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            });
        });
    </script>
</body>
</html>
