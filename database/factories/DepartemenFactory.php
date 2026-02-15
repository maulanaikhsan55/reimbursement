<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartemenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode_departemen' => fake()->unique()->bothify('??##'),
            'nama_departemen' => fake()->word().' Department',
            'deskripsi' => fake()->sentence(),
        ];
    }
}
