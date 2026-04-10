<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesMarketplaceData;
use Tests\TestCase;

class ItemBrowsingTest extends TestCase
{
    use CreatesMarketplaceData;
    use RefreshDatabase;

    /** ゲストでも商品一覧に全商品が表示されることを確認する */
    public function test_item_index_shows_all_items_to_guests(): void
    {
        $seller = $this->createUser();
        $watch = $this->createItem($seller, ['name' => '腕時計']);
        $pc = $this->createItem($seller, ['name' => 'ノートPC']);

        $response = $this->get(route('items.index'));

        $response->assertOk();
        $response->assertSee($watch->name);
        $response->assertSee($pc->name);
    }

    /** 購入済み商品にSOLD表示が付くことを確認する */
    public function test_sold_items_are_marked_as_sold_on_item_index(): void
    {
        $seller = $this->createUser();
        $buyer = $this->createUser(['email' => 'buyer@example.com']);
        $item = $this->createItem($seller, ['name' => '売り切れ商品']);
        $this->purchaseItem($buyer, $item);

        $response = $this->get(route('items.index'));

        $response->assertOk();
        $response->assertSee('SOLD');
        $response->assertSee('売り切れ商品');
    }

    /** ログインユーザーには自分が出品した商品が一覧に表示されないことを確認する */
    public function test_authenticated_user_does_not_see_their_own_items(): void
    {
        $seller = $this->createUser();
        $otherSeller = $this->createUser(['email' => 'other@example.com']);
        $this->createItem($seller, ['name' => '自分の商品']);
        $otherItem = $this->createItem($otherSeller, ['name' => '他人の商品']);

        $response = $this->actingAs($seller)->get(route('items.index'));

        $response->assertOk();
        $response->assertDontSee('自分の商品');
        $response->assertSee($otherItem->name);
    }

    /** マイリストにはいいねした商品だけが表示されることを確認する */
    public function test_mylist_only_shows_liked_items(): void
    {
        $user = $this->createUser();
        $seller = $this->createUser(['email' => 'seller@example.com']);
        $likedItem = $this->createItem($seller, ['name' => 'いいね商品']);
        $notLikedItem = $this->createItem($seller, ['name' => '未いいね商品']);
        $this->likeItem($user, $likedItem);

        $response = $this->actingAs($user)->get(route('items.index', ['tab' => 'mylist']));

        $response->assertOk();
        $response->assertSee('いいね商品');
        $response->assertDontSee('未いいね商品');
    }

    /** マイリスト上でも購入済み商品にSOLD表示が付くことを確認する */
    public function test_mylist_marks_sold_items_as_sold(): void
    {
        $user = $this->createUser();
        $seller = $this->createUser(['email' => 'seller@example.com']);
        $buyer = $this->createUser(['email' => 'buyer@example.com']);
        $item = $this->createItem($seller, ['name' => 'マイリスト売り切れ']);
        $this->likeItem($user, $item);
        $this->purchaseItem($buyer, $item);

        $response = $this->actingAs($user)->get(route('items.index', ['tab' => 'mylist']));

        $response->assertOk();
        $response->assertSee('SOLD');
        $response->assertSee('マイリスト売り切れ');
    }

    /** 未ログイン時にマイリストを開くと空状態の案内が表示されることを確認する */
    public function test_mylist_shows_empty_state_for_guests(): void
    {
        $response = $this->get(route('items.index', ['tab' => 'mylist']));

        $response->assertOk();
        $response->assertSee('マイリストを見るには');
    }

    /** 商品名のキーワードで一覧検索できることを確認する */
    public function test_items_can_be_filtered_by_keyword(): void
    {
        $seller = $this->createUser();
        $matchedItem = $this->createItem($seller, ['name' => '赤いバッグ']);
        $this->createItem($seller, ['name' => '青いシャツ']);

        $response = $this->get(route('items.index', ['keyword' => 'バッグ']));

        $response->assertOk();
        $response->assertSee($matchedItem->name);
        $response->assertDontSee('青いシャツ');
    }

    /** 検索キーワードがマイリスト切り替え後も保持されることを確認する */
    public function test_keyword_is_kept_when_switching_to_mylist(): void
    {
        $user = $this->createUser();
        $seller = $this->createUser(['email' => 'seller@example.com']);
        $matchedItem = $this->createItem($seller, ['name' => '赤いバッグ']);
        $this->likeItem($user, $matchedItem);

        $response = $this->actingAs($user)->get(route('items.index', [
            'tab' => 'mylist',
            'keyword' => 'バッグ',
        ]));

        $response->assertOk();
        $response->assertSee('赤いバッグ');
        $response->assertSee('value="バッグ"', false);
    }
}
