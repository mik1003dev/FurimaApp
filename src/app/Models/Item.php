<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Item extends Model
{
    use HasFactory;

    public const CONDITION_LABELS = [
        1 => '良好',
        2 => '目立った傷や汚れなし',
        3 => 'やや傷や汚れあり',
        4 => '状態が悪い',
    ];

    public const CATEGORY_LABELS = [
        1 => 'ファッション',
        2 => '家電',
        3 => 'インテリア',
        4 => 'レディース',
        5 => 'メンズ',
        6 => 'コスメ',
        7 => '本',
        8 => 'ゲーム',
        9 => 'スポーツ',
        10 => 'キッチン',
        11 => 'ハンドメイド',
        12 => 'アクセサリー',
        13 => 'おもちゃ',
        14 => 'ベビー・キッズ',
    ];

    /**
     * 一括代入を許可するカラム
     * テーブル定義とカラム名を完全一致させる
     */
    protected $fillable = [
        'user_id',
        'name',
        'brand',
        'description',
        'price',
        'condition',
    ];

    /**
     * 型キャスト
     * boolean / int などテーブル定義に合わせておく
     */
    protected $casts = [
        'price'    => 'integer',
        'condition' => 'integer',
    ];

    /**
     * 出品者（usersテーブル）とのリレーション
     * Blade の $item->seller で使う
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 画像（item_imagesテーブル）とのリレーション
     */
    public function images()
    {
        return $this->hasMany(ItemImage::class);
    }

    /**
     * メイン画像（item_images の is_main = true）
     */
    public function mainImage()
    {
        return $this->hasOne(ItemImage::class)->where('is_main', true);
    }

    /**
     * いいね（likesテーブル）とのリレーション
     */
    public function likes()
    {
        return $this->hasMany(\App\Models\Like::class);
    }

    /**
     * コメント（commentsテーブル）とのリレーション
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 注文（ordersテーブル）とのリレーション
     */
    public function order()
    {
        return $this->hasOne(Order::class);
    }

    /**
     * 注文（ordersテーブル）とのリレーション（複数取得用）
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * 一覧・詳細画面で使う画像URLアクセサ
     * Blade 側では $item->image_url で参照する想定
     */
    public function getImageUrlAttribute(): string
    {
        $path = $this->mainImage?->path;

        if ($path) {
            // S3 などの絶対URLはそのまま返す
            if (preg_match('/^https?:\/\//i', $path)) {
                return $path;
            }

            // ローカル保存パスは storage 配下として解決
            return asset('storage/' . ltrim($path, '/'));
        }

        return asset('images/no-image.png');
    }

    public function getConditionLabelAttribute(): string
    {
        $id = (int) ($this->condition ?? 0);
        return self::CONDITION_LABELS[$id] ?? '未設定';
    }

    public function itemCategories()
    {
        return $this->hasMany(ItemCategory::class);
    }

    public function getCategoryLabelsAttribute(): array
    {
        $codes = $this->itemCategories->pluck('category_code')->all();

        return array_map(function ($code) {
            return self::CATEGORY_LABELS[$code] ?? '未設定';
        }, $codes);
    }
    public function getIsSoldAttribute(): bool
    {
        return $this->order()->exists();
    }
}
