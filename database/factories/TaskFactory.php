<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'company_id'     => Company::factory(),
            'project_id'     => Project::factory(),
            'title'          => $this->faker->sentence(4),
            'description'    => $this->faker->optional()->paragraph(),
            'status'         => TaskStatus::Todo,
            'progress'       => 0,
            'blocked_reason' => null,
            'due_date'       => null,
            'milestone'      => null,
            'wbs'            => null,
            'priority'       => 'medium',
            'start_date'     => null,
        ];
    }

    public function todo(): static
    {
        return $this->state(['status' => TaskStatus::Todo, 'progress' => 0]);
    }

    public function doing(): static
    {
        return $this->state(['status' => TaskStatus::Doing, 'progress' => $this->faker->numberBetween(10, 90)]);
    }

    public function blocked(): static
    {
        return $this->state([
            'status'         => TaskStatus::Blocked,
            'blocked_reason' => $this->faker->sentence(),
        ]);
    }

    public function done(): static
    {
        return $this->state(['status' => TaskStatus::Done, 'progress' => 100]);
    }
}
