<?php

namespace Database\Seeders;

use App\Helpers\Constant;
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
            'role' => Constant::ADMIN,
            'first_name' => 'Lewis',
            'last_name' => 'Hamilton',
            'email' => 'lewis@mailinator.com',
            'password' => bcrypt('12345678'),
            'status' => Constant::APPROVED
        ];

        \App\Models\User::create($admin);
    }
}
