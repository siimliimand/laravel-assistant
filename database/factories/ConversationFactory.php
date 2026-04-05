<?php

namespace Database\Factories;

use App\Enums\ConversationStatus;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => auth()->id() ?? User::factory(),
            'title' => fake()->sentence(3),
            'status' => ConversationStatus::Active,
        ];
    }

    /**
     * Indicate that the conversation is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ConversationStatus::Active,
        ]);
    }

    /**
     * Indicate that the conversation is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ConversationStatus::Archived,
        ]);
    }

    /**
     * Indicate that the conversation is deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ConversationStatus::Deleted,
        ]);
    }

    /**
     * Create conversation with a specific number of messages.
     */
    public function withMessages(int $count = 3): static
    {
        return $this->has(
            Message::factory()->count($count),
            'messages'
        );
    }

    /**
     * Create conversation with alternating user/assistant messages.
     */
    public function withConversation(int $exchanges = 2): static
    {
        return $this->afterCreating(function (Conversation $conversation) use ($exchanges) {
            for ($i = 0; $i < $exchanges; $i++) {
                Message::factory()->user()->create([
                    'conversation_id' => $conversation->id,
                    'content' => fake()->sentence(),
                ]);

                Message::factory()->assistant()->create([
                    'conversation_id' => $conversation->id,
                    'content' => fake()->paragraph(),
                ]);
            }
        });
    }
}
