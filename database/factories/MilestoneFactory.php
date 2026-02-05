<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Milestone>
 */
class MilestoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'position' => 0,
            'target_date' => fake()->optional()->dateTimeBetween('now', '+3 months'),
            'is_completed' => fake()->boolean(20),
            'completed_at' => fake()->optional(0.2)->dateTime(),
        ];
    }
}
