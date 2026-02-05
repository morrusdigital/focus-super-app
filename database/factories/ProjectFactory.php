<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->words(3, true) . ' Project',
            'description' => fake()->paragraph(),
            'manager_id' => User::factory(),
            'status' => fake()->randomElement(['planning', 'active', 'on_hold', 'completed', 'cancelled']),
            'start_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+6 months'),
            'budget' => fake()->randomFloat(2, 10000, 500000),
        ];
    }
}
