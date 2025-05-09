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
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
                'timezone' => 'Asia/Jerusalem',
            ],
            [
                'name' => 'Admin',
                'email' => 'hillelcoren+admin@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
                'timezone' => 'Asia/Jerusalem',
            ],
            [
                'name' => 'Follower',
                'email' => 'hillelcoren+follower@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
                'timezone' => 'Asia/Jerusalem',
            ],
        ]);

        \DB::table('roles')->insert([
            [
                'name' => 'Truklin',
                'type' => 'venue',
                'user_id' => 1,
                'subdomain' => 'truklin',
                'description' => 'Music Venue',
                'email' => 'truklin@example.com',
                'email_verified_at' => now(),
                'phone' => '(212) 555-1010',
                'website' => 'https://google.com',
                'city' => 'New York',
                'postal_code' => '10022',
                'timezone' => 'Asia/Jerusalem',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        \DB::table('role_user')->insert([
            [
                'user_id' => 1,
                'role_id' => 1,
                'level' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'role_id' => 1,
                'level' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'role_id' => 1,
                'level' => 'follower',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
