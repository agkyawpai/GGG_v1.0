<?php

namespace Database\Factories;

use App\Models\Biker;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\Township;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'assigned', 'in_transit', 'delivered', 'settled']);
        $type   = $this->faker->randomElement(['type_a', 'type_b']);
        $biker  = ($status !== 'pending') ? Biker::inRandomOrder()->first()?->id : null;

        $orderedAt = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'ulid'         => strtolower((string) Str::ulid()),
            'type'         => $type,
            'status'       => $status,
            'shop_id'      => Shop::inRandomOrder()->first()?->id ?? Shop::factory(),
            'customer_id'  => Customer::inRandomOrder()->first()?->id ?? Customer::factory(),
            'biker_id'     => $biker,
            'township_id'  => Township::inRandomOrder()->first()?->id ?? Township::factory(),
            'item_name'    => $this->faker->word() . ' ' . $this->faker->randomElement(['Phone', 'Bag', 'Shoes', 'Watch', 'Book']),
            'item_cost'    => $this->faker->randomFloat(2, 5000, 500000),
            'cod_amount'   => $this->faker->randomFloat(2, 5000, 550000),
            'delivery_fee' => $this->faker->randomElement([2000, 2500, 3000, 3500]),
            'notes'        => $this->faker->optional()->sentence(),
            'ordered_at'   => $orderedAt,
            'delivered_at' => in_array($status, ['delivered', 'settled', 'returned'])
                ? $this->faker->dateTimeBetween($orderedAt, 'now')
                : null,
            'settled_at'   => $status === 'settled'
                ? $this->faker->dateTimeBetween($orderedAt, 'now')
                : null,
        ];
    }
}
