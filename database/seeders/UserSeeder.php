<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            'first_name'=>'Clare',
            'last_name'=>'Wanjiru',
            'email'=>'admin@kakuzi.com',
            'phone_number'=>'0712345678',
            'password' => Hash::make('admin123'),
            'role' =>'Admin',
            'status' =>'Active',
        ]);
    }
}
