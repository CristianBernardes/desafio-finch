<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
    public function definition(): array
    {
        $statuses = [
            Task::STATUS_PENDING,
            Task::STATUS_IN_PROGRESS,
            Task::STATUS_COMPLETED,
        ];

        $status = $this->faker->randomElement($statuses);

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'status' => $status,
            'assigned_to' => User::inRandomOrder()->first()?->id ?? 1,
            'completed_in' => $status === Task::STATUS_COMPLETED
                ? $this->faker->dateTimeBetween('-30 days', 'now')
                : null,
        ];
    }

    /**
     * Indicate that the task is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Task::STATUS_PENDING,
            'completed_in' => null,
        ]);
    }

    /**
     * Indicate that the task is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Task::STATUS_IN_PROGRESS,
            'completed_in' => null,
        ]);
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Task::STATUS_COMPLETED,
            'completed_in' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }
}
