<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-' . fake()->unique()->numberBetween(10000, 99999),
            'customer_id' => Customer::factory(),
            'created_by' => User::factory(),
            'delivery_address' => fake()->address(),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['Ordered', 'In process', 'In route', 'Delivered']),
            'is_deleted' => false,
            'order_date' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
