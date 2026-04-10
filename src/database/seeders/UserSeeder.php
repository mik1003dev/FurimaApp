<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    public function run()
    {
        $defaultAvatarPath = $this->ensureDefaultAvatar();

        $users = [
            [
                'name' => 'テスト出品者ユーザー',
                'email' => 'u1@test.com',
                'password' => Hash::make('password1'),
                'email_verified_at' => now(),
                'postal_code' => '150-0001',
                'address' => '東京都渋谷区神宮前1-1-1',
                'building' => 'フリママンション101',
                'avatar_path' => $defaultAvatarPath,
                'profile_completed_at' => now(),
            ],
            [
                'name' => 'テスト購入者ユーザー',
                'email' => 'u2@test.com',
                'password' => Hash::make('password2'),
                'email_verified_at' => now(),
                'postal_code' => '160-0022',
                'address' => '東京都新宿区新宿2-2-2',
                'building' => 'マーケットビル202',
                'avatar_path' => $defaultAvatarPath,
                'profile_completed_at' => now(),
            ],
            [
                'name' => 'テストアクティブユーザー',
                'email' => 'u3@test.com',
                'password' => Hash::make('password3'),
                'email_verified_at' => now(),
                'postal_code' => '220-0005',
                'address' => '神奈川県横浜市西区南幸3-3-3',
                'building' => 'フリマタワー303',
                'avatar_path' => $defaultAvatarPath,
                'profile_completed_at' => now(),
            ],
            [
                'name' => 'テスト未認証ユーザー',
                'email' => 'u4@test.com',
                'password' => Hash::make('password4'),
            ],
            [
                'name' => 'テストプロフィール未完了ユーザー',
                'email' => 'u5@test.com',
                'password' => Hash::make('password5'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }

    private function ensureDefaultAvatar(): string
    {
        $disk = Storage::disk('public');
        $avatarPath = 'avatars/default-avatar.png';

        if ($disk->exists($avatarPath)) {
            return $avatarPath;
        }

        $sourcePath = public_path('images/default-avatar.png');

        if (is_file($sourcePath)) {
            $disk->put($avatarPath, file_get_contents($sourcePath));
        }

        return $avatarPath;
    }
}
