<?php

namespace Database\Factories;

use App\Models\Obat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Obat>
 */
class ObatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_obat' => fake()->words(2, true),
            'kemasan' => fake()->randomElement(['Strip', 'Botol', 'Tube']),
            'harga' => fake()->numberBetween(1000, 500000),
        ];
    }
}
