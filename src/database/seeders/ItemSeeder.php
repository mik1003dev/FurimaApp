<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\ItemImage;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // truncate は外部キー制約の都合で一旦使わない
        // Item::truncate();
        // ItemImage::truncate();

        $userId = 1;   // 実在するユーザーIDに合わせて必要なら変更

        // condition の数値マッピング
        $conditionMap = [
            '良好'                 => 1,
            '目立った傷や汚れなし' => 2,
            'やや傷や汚れあり'     => 3,
            '状態が悪い'           => 4,
        ];

        // category は一旦すべて 1（あとで正式カテゴリに合わせて変更）
        $defaultCategory = 1;

        $items = [
            [
                'user_id'     => $userId,
                'name'        => '腕時計',
                'price'       => 15000,
                'brand'       => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
                'category'    => $defaultCategory,
                'condition'   => $conditionMap['良好'],
                'is_sold'     => false,
            ],
            [
                'user_id'     => $userId,
                'name'        => 'HDD',
                'price'       => 5000,
                'brand'       => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
                'category'    => $defaultCategory,
                'condition'   => $conditionMap['目立った傷や汚れなし'],
                'is_sold'     => false,
            ],
            [
                'user_id'     => $userId,
                'name'        => '玉ねぎ3束',
                'price'       => 300,
                'brand'       => 'なし',
                'description' => '新鮮な玉ねぎ3束のセット',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
                'category'    => $defaultCategory,
                'condition'   => $conditionMap['やや傷や汚れあり'],
                'is_sold'     => false,
            ],
            [
                'user_id'     => $userId,
                'name'        => '革靴',
                'price'       => 4000,
                'brand'       => null,
                'description' => 'クラシックなデザインの革靴',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
                'category'    => $defaultCategory,
                'condition'   => $conditionMap['状態が悪い'],
                'is_sold'     => false,
            ],
            [
                'user_id'     => $userId,
                'name'        => 'ノートPC',
                'price'       => 45000,
                'brand'       => null,
                'description' => '高性能なノートパソコン',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
                'category'    => $defaultCategory,
                'condition'   => $conditionMap['良好'],
                'is_sold'     => false,
            ],
            [
                'user_id'     => $userId,
                'name'        => 'マイク',
                'price'       => 8000,
                'brand'       => 'なし',
                'description' => '高音質のレコーディング用マイク',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
                'category'    => $defaultCategory,
                'condition'   => $conditionMap['目立った傷や汚れなし'],
                'is_sold'     => false,
            ],
            [
                'user_id'     => $userId,
                'name'        => 'ショルダーバッグ',
                'price'       => 3500,
                'brand'       => null,
                'description' => 'おしゃれなショルダーバッグ',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
                'category'    => $defaultCategory,
                'condition'   => $conditionMap['やや傷や汚れあり'],
                'is_sold'     => false,
            ],
            [
                'user_id'     => $userId,
                'name'        => 'タンブラー',
                'price'       => 500,
                'brand'       => 'なし',
                'description' => '使いやすいタンブラー',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
                'category'    => $defaultCategory,
                'condition'   => $conditionMap['状態が悪い'],
                'is_sold'     => false,
            ],
            [
                'user_id'     => $userId,
                'name'        => 'コーヒーミル',
                'price'       => 4000,
                'brand'       => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
                'category'    => $defaultCategory,
                'condition'   => $conditionMap['良好'],
                'is_sold'     => false,
            ],
            [
                'user_id'     => $userId,
                'name'        => 'メイクセット',
                'price'       => 2500,
                'brand'       => null,
                'description' => '便利なメイクアップセット',
                'image_url'   => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
                'category'    => $defaultCategory,
                'condition'   => $conditionMap['目立った傷や汚れなし'],
                'is_sold'     => false,
            ],
        ];

        foreach ($items as $data) {
            // image_url だけ一旦退避
            $imageUrl = $data['image_url'] ?? null;
            unset($data['image_url']);

            // items テーブルに INSERT
            $item = Item::create($data);

            // 画像URLがあれば item_images テーブルに INSERT
            if ($imageUrl) {
                ItemImage::create([
                    'item_id'   => $item->id,
                    'path' => $imageUrl,
                    'is_main' => true,      // メイン画像として登録
                ]);
            }
        }
    }
}
