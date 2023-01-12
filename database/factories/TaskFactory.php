<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
 use App\Models\User;
use App\Models\Board;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => fake()->title(),
            'description' => fake()->paragraph(),
            'image' => null,
            'due_date'=> null,
            'current_status'=> null,
            'user_id' => User::factory(),
            'user_id' => Board::factory(),
        ];
    }
}
