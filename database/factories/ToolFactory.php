<?php

namespace Database\Factories;

use App\Models\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tool>
 */
class ToolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucwords(fake()->unique()->words(3, true));
        $code = strtoupper(fake()->unique()->bothify('???-###'));
        $totalStock = fake()->numberBetween(1, 10);

        return [
            'code' => $code,
            'name' => $name,
            'slug' => Str::slug($name.' '.$code),
            'specification' => fake()->sentence(),
            'image_path' => null,
            'total_stock' => $totalStock,
            'available_stock' => $totalStock,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the tool is hidden from the public e-catalog.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the tool has no available stock left.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'available_stock' => 0,
        ]);
    }
}
