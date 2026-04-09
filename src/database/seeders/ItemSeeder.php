<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // condition の数値マッピング
        $conditionMap = [
            '良好'                 => 1,
            '目立った傷や汚れなし' => 2,
            'やや傷や汚れあり'     => 3,
            '状態が悪い'           => 4,
        ];

        // カテゴリコード（Item.phpのCATEGORY_LABELSのキーに合わせる）
        // 1:ファッション, 2:家電, 3:インテリア, 4:レディース, 5:,
        // 6:コスメ, 7:本・音楽・ゲーム, 8:スポーツ・レジャー, 9:ハンドメイド, 10:その他

        $items = [
            [
                'user_id'     => 1,
                'name'        => '腕時計',
                'price'       => 15000,
                'brand'       => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
                'categories'  => [5, 1], // メンズ + ファッション（例）
                'condition'   => $conditionMap['良好'],
            ],
            [
                'user_id'     => 1,
                'name'        => 'HDD',
                'price'       => 5000,
                'brand'       => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
                'categories'  => [2],
                'condition'   => $conditionMap['目立った傷や汚れなし'],
            ],
            [
                'user_id'     => 1,
                'name'        => '玉ねぎ3束',
                'price'       => 300,
                'brand'       => 'なし',
                'description' => '新鮮な玉ねぎ3束のセット',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
                'categories'  => [10],
                'condition'   => $conditionMap['やや傷や汚れあり'],
            ],
            [
                'user_id'     => 2,
                'name'        => '革靴',
                'price'       => 4000,
                'brand'       => null,
                'description' => 'クラシックなデザインの革靴',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
                'categories'  => [5, 1],
                'condition'   => $conditionMap['状態が悪い'],
            ],
            [
                'user_id'     => 2,
                'name'        => 'ノートPC',
                'price'       => 45000,
                'brand'       => null,
                'description' => '高性能なノートパソコン',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
                'categories'  => [2],
                'condition'   => $conditionMap['良好'],
            ],
            [
                'user_id'     => 2,
                'name'        => 'マイク',
                'price'       => 8000,
                'brand'       => 'なし',
                'description' => '高音質のレコーディング用マイク',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
                'categories'  => [2],
                'condition'   => $conditionMap['目立った傷や汚れなし'],
            ],
            [
                'user_id'     => 3,
                'name'        => 'ショルダーバッグ',
                'price'       => 3500,
                'brand'       => null,
                'description' => 'おしゃれなショルダーバッグ',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
                'categories'  => [4, 1], // レディース + ファッション
                'condition'   => $conditionMap['やや傷や汚れあり'],
            ],
            [
                'user_id'     => 3,
                'name'        => 'タンブラー',
                'price'       => 500,
                'brand'       => 'なし',
                'description' => '使いやすいタンブラー',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
                'categories'  => [3],
                'condition'   => $conditionMap['状態が悪い'],
            ],
            [
                'user_id'     => 3,
                'name'        => 'コーヒーミル',
                'price'       => 4000,
                'brand'       => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
                'categories'  => [3],
                'condition'   => $conditionMap['良好'],
            ],
            [
                'user_id'     => 3,
                'name'        => 'メイクセット',
                'price'       => 2500,
                'brand'       => null,
                'description' => '便利なメイクアップセット',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
                'categories'  => [6],
                'condition'   => $conditionMap['目立った傷や汚れなし'],
            ],
        ];

        DB::transaction(function () use ($items) {
            foreach ($items as $data) {
                // image_urlを退避
                $imageUrl = $data['image_url'] ?? null;
                unset($data['image_url']);

                // categoriesを退避してitem_categoriesへ登録する
                $categoryCodes = $data['categories'] ?? [];
                unset($data['categories']);

                // items テーブルに INSERT
                $item = Item::create($data);

                // item_images に INSERT（メイン画像）
                if ($imageUrl) {
                    ItemImage::create([
                        'item_id' => $item->id,
                        'path'    => $imageUrl,
                        'is_main' => true,
                    ]);
                }

                // item_categories に INSERT（複数カテゴリ）
                $categoryCodes = array_values(array_unique($categoryCodes));
                foreach ($categoryCodes as $code) {
                    DB::table('item_categories')->updateOrInsert(
                        ['item_id' => $item->id, 'category_code' => $code],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        });
    }
}
