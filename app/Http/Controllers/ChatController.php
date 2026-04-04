<?php

namespace App\Http\Controllers;

use App\Actions\CreateConversationAction;
use App\Actions\GetConversationAction;
use App\Actions\ListConversationsAction;
use App\Actions\PrepareChatViewAction;
use App\Actions\SendMessageAction;
use App\DTOs\ConversationData;
use App\DTOs\MessageData;
use App\Models\Conversation;
use App\Services\ResponseFormatter;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected ResponseFormatter $formatter
    ) {}

    /**
     * Display the chat interface.
     */
    public function show(?Conversation $conversation, ListConversationsAction $listAction, PrepareChatViewAction $prepareAction)
    {
        $conversations = $listAction->execute(50);
        $viewModel = $prepareAction->execute($conversation, $conversations);

        return view('chat', [
            'viewModel' => $viewModel,
            'conversation' => $viewModel->getCurrentConversation(),
            'messages' => $viewModel->getCurrentConversation()?->messages ?? collect(),
            'conversations' => $conversations,
        ]);
    }

    /**
     * Return JSON list of conversations (limited to 50, sorted by created_at desc).
     */
    public function listConversations(ListConversationsAction $listAction): JsonResponse
    {
        $conversations = $listAction->execute(50);

        return response()->json(['conversations' => $this->formatter->formatConversationsList($conversations)]);
    }

    /**
     * Create a new empty conversation and return JSON response.
     */
    public function createConversation(Request $request, CreateConversationAction $action): JsonResponse
    {
        $data = new ConversationData(
            title: $request->input('title'),
            initialMessage: $request->input('message'),
        );

        $conversation = $action->execute($data);

        return response()->json(['success' => true, 'conversation' => $this->formatter->formatConversation($conversation)]);
    }

    /**
     * Return conversation details and messages as JSON (for AJAX loading).
     */
    public function getConversation(Conversation $conversation, GetConversationAction $action): JsonResponse
    {
        $conversation = $action->execute($conversation->id);

        if (! $conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        return response()->json([
            'conversation' => $this->formatter->formatConversation($conversation),
            'messages' => $this->formatter->formatMessages($conversation->messages),
        ]);
    }

    /**
     * Handle message submission and get AI response.
     */
    public function sendMessage(Request $request, SendMessageAction $action): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
            'conversation_id' => 'nullable|exists:conversations,id',
        ]);

        $data = MessageData::fromRequest($request);

        try {
            $response = $action->execute($data);

            return $this->formatter->handleSendMessageResponse($response, $request);
        } catch (Exception $e) {
            return $this->formatter->handleSendMessageError($request, $e->getMessage());
        }
    }
}
