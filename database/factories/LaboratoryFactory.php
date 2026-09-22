<?php

namespace Database\Factories;

use App\Models\Laboratory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Laboratory>
 */
class LaboratoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Laboratorium Proses Produksi',
            'Laboratorium Konversi Energi & Otomotif',
            'Laboratorium Material & Metalurgi',
            'Laboratorium CNC, CAD/CAM & Otomasi',
            'Laboratorium Pengelasan & Fabrikasi',
            'Laboratorium Termodinamika',
            'Laboratorium Mekanika Teknik',
            'Laboratorium Gambar Teknik',
        ]);

        return [
            'name' => $name,
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'slug' => Str::slug($name),
            'location' => fake()->randomElement([
                'Gedung D Lantai 1',
                'Gedung D Lantai 2',
                'Bengkel Fabrikasi',
            ]),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
