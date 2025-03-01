<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['category_id' => 1, 'name' => 'iPhone 15', 'description' => 'Latest Apple smartphone', 'price' => 1200],
            ['category_id' => 1, 'name' => 'MacBook Air', 'description' => 'Lightweight laptop', 'price' => 1500],
            ['category_id' => 2, 'name' => 'Nike T-Shirt', 'description' => 'Comfortable sportswear', 'price' => 30],
            ['category_id' => 3, 'name' => 'Atomic Habits', 'description' => 'Best-selling self-help book', 'price' => 20],
            ['category_id' => 4, 'name' => 'Air Fryer', 'description' => 'Healthy cooking appliance', 'price' => 90],
            ['category_id' => 5, 'name' => 'LEGO Star Wars', 'description' => 'Fun building set', 'price' => 50],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
