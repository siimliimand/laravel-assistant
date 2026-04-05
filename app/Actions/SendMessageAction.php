<?php

namespace App\Actions;

use App\Ai\Agents\DevBot;
use App\DTOs\MessageData;
use App\DTOs\SendMessageResponse;
use App\Enums\MessageRole;
use App\Exceptions\AiApiException;
use App\Models\Conversation;
use App\Models\Message;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Action to send a message and get AI response.
 *
 * Handles the complete message flow:
 * 1. Get or create conversation
 * 2. Save user message
 * 3. Get AI response from DevBot
 * 4. Save assistant message
 * 5. Handle errors gracefully
 *
 * Usage:
 * ```php
 * $action = app(SendMessageAction::class);
 *
 * try {
 *     $result = $action->execute(
 *         MessageData::fromRequest($request),
 *         $user
 *     );
 *
 *     echo $result['assistant_message']->content;
 *     echo $result['conversation']->id;
 * } catch (\Exception $e) {
 *     // Handle error
 * }
 * ```
 */
class SendMessageAction extends BaseAction
{
    /**
     * Create a new SendMessageAction instance.
     *
     * @param  callable(Conversation): DevBot  $devBotFactory  Factory to create DevBot instances
     */
    public function __construct(
        protected $devBotFactory
    ) {}

    /**
     * Execute the message sending flow.
     *
     * @param  MessageData  $data  Message data transfer object
     * @return SendMessageResponse Response DTO with conversation and message data
     *
     * @throws Exception If AI API call fails
     */
    public function execute(MessageData $data): SendMessageResponse
    {
        // Get or create conversation
        $conversation = $this->getOrCreateConversation($data->conversationId, $data->content);

        // Save user message
        $userMessage = $this->saveUserMessage($conversation, $data->content);

        try {
            // Get AI response using DevBot agent
            $devBot = ($this->devBotFactory)($conversation);
            $response = $devBot->prompt($data->content);

            // Save assistant message
            $assistantMessage = $this->saveAssistantMessage($conversation, $response->text);

            return SendMessageResponse::success($conversation, $assistantMessage);
        } catch (Exception $e) {
            // Wrap the exception with domain-specific context
            $aiException = new AiApiException(
                'Failed to get AI response: '.$e->getMessage(),
                conversationId: $conversation->id,
                userId: auth()->id(),
                previous: $e
            );

            // Log the error with full context
            Log::error('DevBot API error: '.$e->getMessage(), [
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
                'exception' => $aiException,
            ]);

            throw $aiException;
        }
    }

    /**
     * Get existing conversation or create a new one.
     */
    protected function getOrCreateConversation(?int $conversationId, string $initialMessage): Conversation
    {
        if ($conversationId) {
            $conversation = Conversation::where('user_id', auth()->id())->find($conversationId);

            if ($conversation) {
                return $conversation;
            }
        }

        // Create new conversation with title from first message
        $conversation = Conversation::create([
            'title' => 'New Chat',
            'user_id' => auth()->id(),
        ]);

        // Generate title from the initial message
        $conversation->generateTitleFromFirstMessage($initialMessage);

        return $conversation;
    }

    /**
     * Save user message to the conversation.
     */
    protected function saveUserMessage(Conversation $conversation, string $content): Message
    {
        return Message::create([
            'conversation_id' => $conversation->id,
            'role' => MessageRole::User,
            'content' => $content,
        ]);
    }

    /**
     * Save assistant message to the conversation.
     */
    protected function saveAssistantMessage(Conversation $conversation, string $content): Message
    {
        return Message::create([
            'conversation_id' => $conversation->id,
            'role' => MessageRole::Assistant,
            'content' => $content,
        ]);
    }
}
