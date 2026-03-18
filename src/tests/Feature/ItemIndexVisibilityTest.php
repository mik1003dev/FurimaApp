<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemIndexVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_does_not_see_their_own_items_in_item_index(): void
    {
        $seller = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownItem = Item::create([
            'user_id' => $seller->id,
            'name' => '自分の商品',
            'brand' => 'Brand A',
            'description' => '自分が出品した商品です',
            'price' => 3000,
            'condition' => 1,
        ]);

        ItemImage::create([
            'item_id' => $ownItem->id,
            'path' => 'items/own-item.jpg',
            'is_main' => true,
        ]);

        $otherItem = Item::create([
            'user_id' => $otherUser->id,
            'name' => '他人の商品',
            'brand' => 'Brand B',
            'description' => '他人が出品した商品です',
            'price' => 5000,
            'condition' => 2,
        ]);

        ItemImage::create([
            'item_id' => $otherItem->id,
            'path' => 'items/other-item.jpg',
            'is_main' => true,
        ]);

        $response = $this->actingAs($seller)->get(route('items.index'));

        $response->assertOk();
        $response->assertDontSee('自分の商品');
        $response->assertSee('他人の商品');
    }
}
