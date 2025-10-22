<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPhoto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@halcon.com',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        User::factory()->create([
            'name' => 'Sales User',
            'email' => 'sales@halcon.com',
            'password' => Hash::make('password'),
            'role' => 'Sales',
        ]);

        User::factory()->create([
            'name' => 'Warehouse User',
            'email' => 'warehouse@halcon.com',
            'password' => Hash::make('password'),
            'role' => 'Warehouse',
        ]);

        User::factory()->create([
            'name' => 'Route User',
            'email' => 'route@halcon.com',
            'password' => Hash::make('password'),
            'role' => 'Route',
        ]);

        User::factory()->create([
            'name' => 'Purchasing User',
            'email' => 'purchasing@halcon.com',
            'password' => Hash::make('password'),
            'role' => 'Purchasing',
        ]);

        $additionalUsers = User::factory(5)->create();

        $customers = Customer::factory(20)->create();

        $salesUsers = User::where('role', 'Sales')->get();
        $routeUsers = User::where('role', 'Route')->get();

        foreach ($customers as $customer) {
            $ordersCount = rand(1, 5);

            for ($i = 0; $i < $ordersCount; $i++) {
                $order = Order::factory()->create([
                    'customer_id' => $customer->id,
                    'created_by' => $salesUsers->random()->id,
                ]);

                if (in_array($order->status, ['In route', 'Delivered'])) {
                    OrderPhoto::factory()->create([
                        'order_id' => $order->id,
                        'photo_type' => 'loaded',
                        'uploaded_by' => $routeUsers->random()->id,
                    ]);
                }

                if ($order->status === 'Delivered') {
                    OrderPhoto::factory()->create([
                        'order_id' => $order->id,
                        'photo_type' => 'delivered',
                        'uploaded_by' => $routeUsers->random()->id,
                    ]);
                }
            }
        }
    }
}
