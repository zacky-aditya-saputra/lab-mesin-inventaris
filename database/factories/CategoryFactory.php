<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Alat Ukur Presisi',
            'Mesin Perkakas',
            'Alat Las & Fabrikasi',
            'Alat Bantu Produksi',
            'Alat Listrik & Elektronika',
        ]);

        $code = collect(explode(' ', $name))
            ->map(fn (string $word) => Str::substr(preg_replace('/[^A-Za-z]/', '', $word) ?? '', 0, 1))
            ->filter()
            ->take(3)
            ->implode('');

        return [
            'name' => $name,
            'code' => $code,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
        ];
    }
}
