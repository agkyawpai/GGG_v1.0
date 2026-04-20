<?php

namespace Database\Seeders;

use App\Models\Biker;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Settlement;
use App\Models\Shop;
use App\Models\Township;
use App\Models\User;
use App\Models\Warehousing;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@ggg.test',
            'password' => bcrypt('password'),
        ]);

        Township::factory(10)->create();
        Shop::factory(10)->create();
        Customer::factory(20)->create();
        Biker::factory(5)->create();

        $orders = Order::factory(20)->create();

        // Create settlement records for settled orders
        $admin = User::first();
        $orders->where('status', 'settled')->each(function (Order $order) use ($admin) {
            Settlement::create([
                'order_id'      => $order->id,
                'total_cost'    => $order->item_cost,
                'delivery_fee'  => $order->delivery_fee,
                'cod_collected' => $order->cod_amount,
                'net_amount'    => $order->cod_amount - $order->item_cost,
                'settled_by'    => $admin->id,
                'settled_at'    => $order->settled_at ?? now(),
            ]);
        });

        // Create warehousing records for type_b orders
        $orders->where('type', 'type_b')->each(function (Order $order) {
            Warehousing::create([
                'order_id'    => $order->id,
                'status'      => match ($order->status) {
                    'pending'    => 'in_transit',
                    'assigned'   => 'stored',
                    'in_transit' => 'dispatched',
                    default      => 'stored',
                },
                'shipped_via' => fake()->randomElement(['bus', 'express']),
            ]);
        });
    }
}
