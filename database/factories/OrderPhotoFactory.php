<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderPhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'photo_path' => 'photos/' . fake()->uuid() . '.jpg',
            'photo_type' => fake()->randomElement(['loaded', 'delivered']),
            'uploaded_by' => User::factory(),
        ];
    }
}
