<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name'       => $this->faker->company(),
            'type'       => 'company',
            'parent_id'  => null,
            'saldo_awal' => 0,
        ];
    }

    public function holding(): static
    {
        return $this->state(['type' => 'holding', 'parent_id' => null]);
    }
}
