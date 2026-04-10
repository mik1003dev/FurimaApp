<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesMarketplaceData;
use Tests\TestCase;

class ProfileAndSellingTest extends TestCase
{
    use CreatesMarketplaceData;
    use RefreshDatabase;

    /** テスト用のPNGアップロードファイルを生成する */
    private function fakePngUpload(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9sotL7sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    /** プロフィール画面にユーザー情報と出品商品が表示されることを確認する */
    public function test_profile_page_shows_user_information(): void
    {
        $user = $this->createCompletedUser([
            'name' => 'プロフィール太郎',
            'avatar_path' => 'avatars/profile.png',
        ]);
        $this->createItem($user, ['name' => '出品商品A']);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertOk();
        $response->assertSee('プロフィール太郎');
        $response->assertSee('出品商品A');
        $response->assertSee(route('profile.edit'), false);
    }

    /** プロフィール編集画面に現在の登録情報が初期表示されることを確認する */
    public function test_profile_edit_page_prefills_current_user_information(): void
    {
        $user = $this->createCompletedUser([
            'name' => 'プロフィール太郎',
            'postal_code' => '111-2222',
            'address' => '東京都千代田区1-1-1',
            'building' => '皇居前ビル',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('value="プロフィール太郎"', false);
        $response->assertSee('value="111-2222"', false);
        $response->assertSee('value="東京都千代田区1-1-1"', false);
        $response->assertSee('value="皇居前ビル"', false);
    }

    /** プロフィール更新失敗時に一時保存した画像が保持されることを確認する */
    public function test_avatar_is_preserved_when_profile_update_validation_fails(): void
    {
        Storage::fake('public');

        $user = $this->createUser([
            'avatar_path' => null,
            'profile_completed_at' => null,
        ]);

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'avatar' => $this->fakePngUpload('avatar.png'),
            'name' => str_repeat('a', 21),
            'postal_code' => '123-4567',
            'address' => 'Tokyo',
            'building' => 'Building',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors(['name']);
        $response->assertSessionHasInput('avatar_temp_path', function ($path) {
            Storage::disk('public')->assertExists($path);

            return str_starts_with($path, 'avatars/tmp/');
        });
    }

    /** プロフィール情報を更新でき、プロフィール完了日時も設定されることを確認する */
    public function test_profile_can_be_updated(): void
    {
        Storage::fake('public');

        $user = $this->createUser([
            'avatar_path' => null,
            'profile_completed_at' => null,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'avatar' => $this->fakePngUpload('avatar.png'),
            'name' => '更新後ユーザー',
            'postal_code' => '123-4567',
            'address' => '東京都港区芝公園4-2-8',
            'building' => '東京タワー',
        ]);

        $response->assertRedirect(route('profile.show'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '更新後ユーザー',
            'postal_code' => '123-4567',
            'address' => '東京都港区芝公園4-2-8',
            'building' => '東京タワー',
        ]);
        $this->assertNotNull($user->fresh()->profile_completed_at);
    }

    /** 商品出品時に商品本体・画像・カテゴリが保存されることを確認する */
    public function test_item_can_be_listed_for_sale(): void
    {
        Storage::fake('public');

        $seller = $this->createCompletedUser();

        $response = $this->actingAs($seller)->post(route('items.storeImage'), [
            'item_image' => $this->fakePngUpload('item.png'),
            'category_codes' => [1, 2],
            'name' => '新しい出品商品',
            'brand' => 'ブランドA',
            'description' => '出品商品の説明です',
            'condition' => 1,
            'price' => 5500,
        ]);

        $response->assertRedirect(route('items.index'));
        $item = Item::where('name', '新しい出品商品')->firstOrFail();
        $this->assertDatabaseHas('item_images', [
            'item_id' => $item->id,
            'is_main' => true,
        ]);
        $this->assertDatabaseHas('item_categories', [
            'item_id' => $item->id,
            'category_code' => 1,
        ]);
        $this->assertDatabaseHas('item_categories', [
            'item_id' => $item->id,
            'category_code' => 2,
        ]);
    }

    /** 商品出品でカテゴリ未選択エラー時も選択済み画像が保持されることを確認する */
    public function test_item_image_is_preserved_when_listing_validation_fails(): void
    {
        Storage::fake('public');

        $seller = $this->createCompletedUser();

        $response = $this->actingAs($seller)->from(route('items.create'))->post(route('items.storeImage'), [
            'item_image' => $this->fakePngUpload('item.png'),
            'category_codes' => [],
            'name' => '新しい出品商品',
            'brand' => 'ブランドA',
            'description' => '出品商品の説明です',
            'condition' => 1,
            'price' => 5500,
        ]);

        $response->assertRedirect(route('items.create'));
        $response->assertSessionHasErrors(['category_codes']);
        $response->assertSessionHasInput('uploaded_item_image', function ($path) {
            Storage::disk('public')->assertExists($path);

            return str_starts_with($path, 'items/tmp/');
        });
    }
}
