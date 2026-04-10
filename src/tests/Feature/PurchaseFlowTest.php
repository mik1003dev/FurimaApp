<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesMarketplaceData;
use Tests\TestCase;

class PurchaseFlowTest extends TestCase
{
    use CreatesMarketplaceData;
    use RefreshDatabase;

    /** 出品者本人は自分の商品購入画面へ進めないことを確認する */
    public function test_purchase_page_redirects_seller_back_to_item_detail(): void
    {
        $seller = $this->createCompletedUser();
        $item = $this->createItem($seller);

        $response = $this->actingAs($seller)->get(route('purchase.show', $item));

        $response->assertRedirect(route('items.show', $item));
    }

    /** 購入画面にログインユーザーの配送先情報が反映されることを確認する */
    public function test_purchase_page_uses_logged_in_user_shipping_information(): void
    {
        $buyer = $this->createCompletedUser();
        $seller = $this->createCompletedUser(['email' => 'seller@example.com']);
        $item = $this->createItem($seller, ['name' => '購入対象']);

        $response = $this->actingAs($buyer)->get(route('purchase.show', $item));

        $response->assertOk();
        $response->assertSee('購入対象');
        $response->assertSee('123-4567');
        $response->assertSee('東京都渋谷区神南1-1-1');
    }

    /** 変更した配送先住所が購入画面へ反映されることを確認する */
    public function test_shipping_address_can_be_updated_and_reflected_on_purchase_page(): void
    {
        $buyer = $this->createCompletedUser();
        $seller = $this->createCompletedUser(['email' => 'seller@example.com']);
        $item = $this->createItem($seller);

        $updateResponse = $this->actingAs($buyer)->put(route('purchase.address.update', $item), [
            'postal_code' => '987-6543',
            'address' => '東京都新宿区西新宿2-8-1',
            'building' => '新宿ビル 901',
        ]);

        $updateResponse->assertRedirect(route('purchase.show', $item));

        $showResponse = $this->actingAs($buyer)->get(route('purchase.show', $item));
        $showResponse->assertSee('987-6543');
        $showResponse->assertSee('東京都新宿区西新宿2-8-1 新宿ビル 901');
    }

    /** 購入時に支払い方法の選択が必須であることを確認する */
    public function test_purchase_requires_payment_method(): void
    {
        $buyer = $this->createCompletedUser();
        $seller = $this->createCompletedUser(['email' => 'seller@example.com']);
        $item = $this->createItem($seller);

        $response = $this->actingAs($buyer)
            ->from(route('purchase.show', $item))
            ->post(route('purchase.purchase', $item), [
                'payment_method' => '',
            ]);

        $response->assertRedirect(route('purchase.show', $item));
        $response->assertSessionHasErrors([
            'payment_method' => '支払い方法を選択してください',
        ]);
    }

    /** 正常な購入操作でStripeの決済画面へ遷移することを確認する */
    public function test_purchase_redirects_to_stripe_checkout_when_valid(): void
    {
        config()->set('services.stripe.secret', 'sk_test_dummy');
        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/pay/cs_test_123',
            ], 200),
        ]);

        $buyer = $this->createCompletedUser();
        $seller = $this->createCompletedUser(['email' => 'seller@example.com']);
        $item = $this->createItem($seller, ['price' => 4200]);

        $response = $this->actingAs($buyer)->post(route('purchase.purchase', $item), [
            'payment_method' => 'convenience',
        ]);

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');
        $this->assertNotNull(session('stripe_checkout.cs_test_123'));
    }

    /** 購入完了後に注文が保存され、商品がSOLD状態になることを確認する */
    public function test_purchase_completion_creates_order_and_marks_item_as_sold(): void
    {
        $buyer = $this->createCompletedUser();
        $seller = $this->createCompletedUser(['email' => 'seller@example.com']);
        $item = $this->createItem($seller, ['price' => 4200]);

        $this->withSession([
            'stripe_checkout.cs_test_123' => [
                'item_id' => $item->id,
                'user_id' => $buyer->id,
                'price' => $item->price,
                'payment_method' => 'card',
                'shipping_postal_code' => '123-4567',
                'shipping_address' => '東京都渋谷区神南1-1-1',
                'shipping_building' => 'テストビル 101',
            ],
        ]);

        $response = $this->actingAs($buyer)->get(route('purchase.complete', [
            'item_id' => $item,
            'session_id' => 'cs_test_123',
        ]));

        $response->assertRedirect(route('items.index'));
        $response->assertSessionHas('status', '購入が完了しました');
        $this->assertDatabaseHas('orders', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'price' => 4200,
            'payment_method' => 2,
        ]);

        $detailResponse = $this->actingAs($buyer)->get(route('items.show', $item));
        $detailResponse->assertSee('SOLD');
    }

    /** 購入済み商品がプロフィールの購入一覧に表示されることを確認する */
    public function test_purchased_item_appears_in_profile_purchase_tab(): void
    {
        $buyer = $this->createCompletedUser();
        $seller = $this->createCompletedUser(['email' => 'seller@example.com']);
        $item = $this->createItem($seller, ['name' => '購入済み商品']);
        $this->purchaseItem($buyer, $item);

        $response = $this->actingAs($buyer)->get(route('profile.show', ['page' => 'buy']));

        $response->assertOk();
        $response->assertSee('購入済み商品');
    }
}
