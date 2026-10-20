<?php

namespace Database\Factories;

use App\Models\Laboratory;
use App\Models\StudentActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentActivityLog>
 */
class StudentActivityLogFactory extends Factory
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
            'laboratory_id' => Laboratory::factory(),
            'activity_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'time_in' => '08:00',
            'time_out' => '12:00',
            'activity_type' => fake()->randomElement(['Praktikum', 'Penelitian', 'Tugas Akhir', 'Mandiri']),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => 'PENDING',
            'verified_by' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'VERIFIED',
            'verified_by' => User::factory()->laboran(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'REJECTED',
            'verified_by' => User::factory()->laboran(),
        ]);
    }
}
