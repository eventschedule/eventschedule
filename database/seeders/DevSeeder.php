<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('users')->insert([
            [
                'name' => 'Owner',
                'email' => 'hillelcoren+owner@gmail.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Member',
                'email' => 'hillelcoren+member@gmail.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Follower',
                'email' => 'hillelcoren+follower@gmail.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        \DB::table('roles')->insert([
            [
                'name' => 'Truklin',
                'user_id' => 1,
                'subdomain' => 'truklin',
                'description' => 'Music Venue',
                'email' => 'truklin@example.com',
                'phone' => '(212) 555-1010',
                'website' => 'https://google.com',                
            ]
        ]);

        \DB::table('role_user')->insert([
            [
                'user_id' => 1,
                'role_id' => 1,
                'level' => 'owner',
            ],
            [
                'user_id' => 2,
                'role_id' => 1,
                'level' => 'member',
            ],
            [
                'user_id' => 3,
                'role_id' => 1,
                'level' => 'follower',
            ],
        ]);
    }
}
