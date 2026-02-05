<?php

namespace Database\Factories;

use App\Models\Column;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'column_id' => Column::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'assignee_id' => User::factory(),
            'position' => 0,
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+2 months'),
            'progress' => fake()->randomFloat(2, 0, 100),
            'labels' => fake()->optional()->randomElements(['bug', 'feature', 'enhancement', 'documentation'], fake()->numberBetween(0, 3)),
        ];
    }
}
