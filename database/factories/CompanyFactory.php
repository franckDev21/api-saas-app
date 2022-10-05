<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'description' => 'Descrip ...',
            'address' => fake()->address(),
            'country' => fake()->country(),
            'tel' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'number_of_employees' => rand(5,100)
        ];
    }
}
