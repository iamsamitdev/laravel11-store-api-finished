<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'fullname' => 'Admin User',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('12345678'),
                'tel' => '1234567890',
                'avatar' => null,
                'role' => 0,
                'email_verified_at' => now(),
                'remember_token' => \Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fullname' => 'Normal User',
                'username' => 'user',
                'email' => 'user@example.com',
                'password' => Hash::make('12345678'),
                'tel' => '0987654321',
                'avatar' => null,
                'role' => 1,
                'email_verified_at' => now(),
                'remember_token' => \Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fullname' => 'Manager User',
                'username' => 'manager',
                'email' => 'manager@example.com',
                'password' => Hash::make('12345678'),
                'tel' => '1122334455',
                'avatar' => null,
                'role' => 2,
                'email_verified_at' => now(),
                'remember_token' => \Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
