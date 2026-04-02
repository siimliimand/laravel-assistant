<?php

namespace App\Http\Controllers;

use App\Ai\Agents\DevBot;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Display the chat interface.
     */
    public function show(?Conversation $conversation = null): View
    {
        // If no conversation specified, try to get the most recent one with eager loaded messages
        if (! $conversation) {
            $conversation = Conversation::with('messages')->latest()->first();
        } else {
            // Ensure messages are eager loaded for the provided conversation
            $conversation->load('messages');
        }

        $messages = $conversation ? $conversation->messages()->orderBy('created_at', 'asc')->get() : collect();

        // Get all conversations for sidebar (limited to 50 most recent)
        $conversations = Conversation::latest()->limit(50)->get();

        return view('chat', [
            'conversation' => $conversation,
            'messages' => $messages,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Return JSON list of conversations (limited to 50, sorted by created_at desc).
     */
    public function listConversations(): JsonResponse
    {
        $conversations = Conversation::latest()->limit(50)->get();

        return response()->json([
            'conversations' => $conversations->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'created_at' => $conversation->created_at->diffForHumans(),
                    'updated_at' => $conversation->updated_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /**
     * Create a new empty conversation and return JSON response.
     */
    public function createConversation(Request $request): JsonResponse
    {
        $conversation = Conversation::create([
            'title' => 'New Chat',
        ]);

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'created_at' => $conversation->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Return conversation details and messages as JSON (for AJAX loading).
     */
    public function getConversation(Conversation $conversation): JsonResponse
    {
        $conversation->load(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'created_at' => $conversation->created_at->diffForHumans(),
            ],
            'messages' => $conversation->messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->role === 'assistant' ? $message->formattedContent() : $message->content,
                    'created_at' => $message->created_at->format('g:i A'),
                ];
            }),
        ]);
    }

    /**
     * Handle message submission and get AI response.
     */
    public function sendMessage(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
            'conversation_id' => 'nullable|exists:conversations,id',
        ]);

        $conversation = null;

        // Get or create conversation
        if (isset($validated['conversation_id']) && $validated['conversation_id']) {
            $conversation = Conversation::find($validated['conversation_id']);
        }

        if (! $conversation) {
            $conversation = Conversation::create([
                'title' => 'New Chat', // Will be updated from first message
            ]);
        }

        // Save user message
        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // Generate conversation title from first message (only for new conversations with default title)
        if ($conversation->title === 'New Chat' || ! $conversation->title) {
            $conversation->generateTitleFromFirstMessage($validated['message']);
        }

        try {
            // Get AI response using DevBot agent
            $devBot = new DevBot($conversation);
            $response = $devBot->prompt($validated['message']);

            // Save assistant message
            $assistantMessage = Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $response->text,
            ]);

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'response' => $assistantMessage->formattedContent(),
                    'conversation_id' => $conversation->id,
                    'conversation_title' => $conversation->title,
                ]);
            }

            return redirect()->route('chat.show', ['conversation' => $conversation]);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('DevBot API error: '.$e->getMessage(), [
                'conversation_id' => $conversation->id,
                'exception' => $e,
            ]);

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get a response from DevBot. Please try again later.',
                ], 500);
            }

            return redirect()->route('chat.show', ['conversation' => $conversation])
                ->with('error', 'Failed to get a response from DevBot. Please try again later.');
        }
    }
}
