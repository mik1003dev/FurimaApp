<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesMarketplaceData;
use Tests\TestCase;

class ItemDetailInteractionTest extends TestCase
{
    use CreatesMarketplaceData;
    use RefreshDatabase;

    /** 商品詳細ページに必要な情報一式が表示されることを確認する */
    public function test_item_detail_shows_required_information(): void
    {
        $seller = $this->createUser(['name' => '出品者']);
        $item = $this->createItem($seller, [
            'name' => '腕時計',
            'brand' => 'Rolax',
            'description' => 'スタイリッシュな腕時計です',
            'price' => 15000,
            'condition' => 2,
        ], [1, 5], 'https://example.com/watch.jpg');
        $commentUser = $this->createUser(['email' => 'comment@example.com', 'name' => 'コメントユーザー']);
        $this->likeItem($commentUser, $item);
        $this->commentOnItem($commentUser, $item, '気になります');

        $response = $this->get(route('items.show', $item));

        $response->assertOk();
        $response->assertSee('腕時計');
        $response->assertSee('Rolax');
        $response->assertSee('15,000');
        $response->assertSee('スタイリッシュな腕時計です');
        $response->assertSee('ファッション');
        $response->assertSee('メンズ');
        $response->assertSee('目立った傷や汚れなし');
        $response->assertSee('気になります');
        $response->assertSee('コメントユーザー');
    }

    /** ログインユーザーが商品にいいねできることを確認する */
    public function test_logged_in_user_can_like_item(): void
    {
        $user = $this->createUser();
        $seller = $this->createUser(['email' => 'seller@example.com']);
        $item = $this->createItem($seller);

        $response = $this->actingAs($user)->from(route('items.show', $item))->post(route('likes.toggle', $item));

        $response->assertRedirect(route('items.show', $item));
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /** 既に付けたいいねを再操作で解除できることを確認する */
    public function test_like_toggle_removes_existing_like(): void
    {
        $user = $this->createUser();
        $seller = $this->createUser(['email' => 'seller@example.com']);
        $item = $this->createItem($seller);
        $this->likeItem($user, $item);

        $response = $this->actingAs($user)->from(route('items.show', $item))->post(route('likes.toggle', $item));

        $response->assertRedirect(route('items.show', $item));
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /** ログインユーザーがコメントを投稿できることを確認する */
    public function test_logged_in_user_can_post_comment(): void
    {
        $user = $this->createUser();
        $seller = $this->createUser(['email' => 'seller@example.com']);
        $item = $this->createItem($seller);

        $response = $this->actingAs($user)->post(route('comments.store', $item), [
            'body' => 'コメントを投稿します',
        ]);

        $response->assertRedirect(route('items.show', $item));
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'body' => 'コメントを投稿します',
        ]);
    }

    /** 未ログインユーザーはコメント投稿できずログイン画面へ誘導されることを確認する */
    public function test_guest_cannot_post_comment(): void
    {
        $seller = $this->createUser();
        $item = $this->createItem($seller);

        $response = $this->post(route('comments.store', $item), [
            'body' => 'ゲストコメント',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('comments', 0);
    }

    /** コメント入力が必須であることを確認する */
    public function test_comment_validation_requires_body(): void
    {
        $user = $this->createUser();
        $seller = $this->createUser(['email' => 'seller@example.com']);
        $item = $this->createItem($seller);

        $response = $this->actingAs($user)
            ->from(route('items.show', $item))
            ->post(route('comments.store', $item), [
                'body' => '',
            ]);

        $response->assertRedirect(route('items.show', $item));
        $response->assertSessionHasErrors([
            'body' => 'コメントを入力してください',
        ]);
    }

    /** 256文字以上のコメントがバリデーションで拒否されることを確認する */
    public function test_comment_validation_rejects_more_than_255_characters(): void
    {
        $user = $this->createUser();
        $seller = $this->createUser(['email' => 'seller@example.com']);
        $item = $this->createItem($seller);

        $response = $this->actingAs($user)
            ->from(route('items.show', $item))
            ->post(route('comments.store', $item), [
                'body' => str_repeat('あ', 256),
            ]);

        $response->assertRedirect(route('items.show', $item));
        $response->assertSessionHasErrors([
            'body' => 'コメントは255文字以内で入力してください',
        ]);
    }
}
