<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_number' => 'CUST-' . fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->name(),
            'company_name' => fake()->optional()->company(),
            'tax_id' => fake()->optional()->numerify('RFC-###########'),
            'fiscal_address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
        ];
    }
}
