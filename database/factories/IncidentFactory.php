<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::pluck('id')->toArray();
        $companies = Company::pluck('id')->toArray();

        return [
            'start_date' => fake()->date(),
            'start_time' => fake()->time(),
            'close_date' => fake()->date(),
            'close_time' => fake()->time(),
            'type' => fake()->randomElement(['Onsite', 'Remote']),
            'description' => fake()->text(),
            'topic' => fake()->randomElement(['PC', 'Server', 'Network', 'Other']),
            'assigned_id' => fake()->randomElement($users),
            'pic_name' => fake()->name,
            'created_by' => fake()->randomElement($users),
            'company_id' => fake()->randomElement($companies),
            'status' => fake()->randomElement(['Open', 'Escalated', 'WIP', 'Close']),

        ];
    }
}
