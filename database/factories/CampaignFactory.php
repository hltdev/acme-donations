<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'goal_amount' => fake()->numberBetween(100, 1000000),
            'current_amount' => fake()->randomFloat(2, 10, 1000000),
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(rand(2, 12)),
        ];
    }
}
