<?php

namespace App\Ai\Agents;

use App\Models\Conversation;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\MaxSteps;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Provider;
use Laravel\Ai\Temperature;
use Stringable;

#[Provider('z')]
#[MaxSteps(10)]
#[Temperature(0.7)]
class DevBot implements Agent, Conversational, HasTools
{
    use Promptable;

    public function __construct(
        protected ?Conversation $conversation = null
    ) {}

    /**
     * Get the model to use for the agent.
     */
    public function model(): string
    {
        return env('DEVBOT_MODEL', 'claude-haiku-4-5-20251001');
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are DevBot, a development-focused AI assistant integrated into a Laravel application. Your primary purpose is to help developers with programming questions, code review, debugging, architecture decisions, and best practices.

## Core Responsibilities

- Provide accurate, practical answers to programming questions
- Follow Laravel and PHP best practices when applicable
- Write clean, well-commented code examples
- Explain complex concepts in simple terms
- Help with debugging by asking clarifying questions
- Suggest architectural improvements and design patterns

## Response Guidelines

- Always prioritize accuracy over speed
- Include code examples when relevant
- Explain the "why" behind your recommendations, not just the "what"
- When uncertain, acknowledge it and provide your best reasoning
- Keep responses concise but thorough
- Use markdown formatting for code blocks and technical terms

## Language & Tone

- Be professional but approachable
- Use clear, jargon-free language when possible
- Adapt your technical depth to the question's complexity
- Stay focused on development topics

## Boundary Handling

If asked about non-development topics, politely redirect the conversation back to programming and technical assistance while maintaining a helpful tone.
PROMPT;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        if (! $this->conversation) {
            return [];
        }

        return $this->conversation->getMessagesForAgent();
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
}
