<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->numberBetween(2, 150) * 500;

        return [
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'barcode' => fake()->unique()->ean13(),
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(['Minuman', 'Snack', 'Sembako']),
            'price' => $price,
            'cost' => (int) ($price * 0.7),
            'stock' => fake()->numberBetween(0, 50),
            'stock_alert_threshold' => 5,
            'is_active' => true,
        ];
    }
}
