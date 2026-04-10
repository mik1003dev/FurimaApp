<?php

namespace Tests\Support;

use App\Models\Comment;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemImage;
use App\Models\Like;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait CreatesMarketplaceData
{
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => Hash::make('password123'),
        ], $attributes));
    }

    protected function createCompletedUser(array $attributes = []): User
    {
        return $this->createUser(array_merge([
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区神南1-1-1',
            'building' => 'テストビル 101',
            'avatar_path' => 'avatars/test-avatar.png',
            'profile_completed_at' => now(),
        ], $attributes));
    }

    protected function createItem(User $seller, array $attributes = [], array $categoryCodes = [1], ?string $imagePath = null): Item
    {
        $item = Item::create(array_merge([
            'user_id' => $seller->id,
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'テスト商品の説明です',
            'price' => 3000,
            'condition' => 1,
        ], $attributes));

        ItemImage::create([
            'item_id' => $item->id,
            'path' => $imagePath ?? 'items/test-item.png',
            'is_main' => true,
        ]);

        foreach ($categoryCodes as $categoryCode) {
            ItemCategory::create([
                'item_id' => $item->id,
                'category_code' => $categoryCode,
            ]);
        }

        return $item;
    }

    protected function likeItem(User $user, Item $item): Like
    {
        return Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    protected function commentOnItem(User $user, Item $item, string $body = 'コメント本文です'): Comment
    {
        return Comment::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'body' => $body,
        ]);
    }

    protected function purchaseItem(User $buyer, Item $item, array $attributes = []): Order
    {
        return Order::create(array_merge([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'price' => $item->price,
            'payment_method' => 1,
            'shipping_postal_code' => '123-4567',
            'shipping_address' => '東京都港区南青山1-1-1',
            'shipping_building' => '青山ビル 201',
            'status' => 1,
        ], $attributes));
    }
}
