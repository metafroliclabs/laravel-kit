<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@mailinator.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ];

        \App\Models\User::create($admin);
    }
}
