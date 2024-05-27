<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'Mobile', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tablet', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Smart Watch', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Laptop', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Desktop', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Camera', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Headphones', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Speakers', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Accessories', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gaming', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('categories')->insert($categories);
    }
}
