<?php

namespace Database\Factories;

use App\Enums\MessageRole;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role' => MessageRole::User,
            'content' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the message is from a user.
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MessageRole::User,
        ]);
    }

    /**
     * Indicate that the message is from an assistant.
     */
    public function assistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MessageRole::Assistant,
        ]);
    }

    /**
     * Create a message with specific content.
     */
    public function withContent(string $content): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $content,
        ]);
    }

    /**
     * Create a user message with specific content.
     */
    public function userMessage(string $content): static
    {
        return $this->user()->withContent($content);
    }

    /**
     * Create an assistant message with specific content.
     */
    public function assistantMessage(string $content): static
    {
        return $this->assistant()->withContent($content);
    }
}
