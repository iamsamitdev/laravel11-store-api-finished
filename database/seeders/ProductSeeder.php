<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [];

        for ($i = 1; $i <= 100; $i++) {
            $products[] = [
                'name' => 'Product ' . $i,
                'slug' => 'product-' . $i,
                'description' => 'Description for Product ' . $i,
                'price' => rand(1000, 100000) / 100,
                'image' => 'noimg.jpg',
                'user_id' => rand(1, 3), // Assuming you have 3 users created already
                'category_id' => rand(1, 10), // Assuming you have 10 categories
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('products')->insert($products);
    }
}
