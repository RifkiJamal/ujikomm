<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->delete();

        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
            'isAdmin' => '2',
        ]);



        \App\Models\User::create([
            'name' => 'User',
            'email' => 'user@gmail.com',
            'name' => 'dyaa',
            'email' => 'dyaa@gmail.com',
            'kelas_id' => 3,
            'password' => bcrypt('12345678'),
            'isAdmin' => '0',
        ]);
    }
}