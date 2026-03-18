<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'テストユーザー1',
                'email' => 'u1@test.com',
                'password' => Hash::make('password1'),
            ],
            [
                'name' => 'テストユーザー2',
                'email' => 'u2@test.com',
                'password' => Hash::make('password2'),
            ],
            [
                'name' => 'テストユーザー3',
                'email' => 'u3@test.com',
                'password' => Hash::make('password3'),
            ],
            [
                'name' => 'テストユーザー4',
                'email' => 'u4@test.com',
                'password' => Hash::make('password4'),
            ],
            [
                'name' => 'テストユーザー5',
                'email' => 'u5@test.com',
                'password' => Hash::make('password5'),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
