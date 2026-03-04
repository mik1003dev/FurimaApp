<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        7 => '本・音楽・ゲーム',
        8 => 'スポーツ・レジャー',
        9 => 'ハンドメイド',
        10 => 'その他',
    ];


    // テーブル名はデフォルトで "items" なので指定不要だが、明示してもOK
    // protected $table = 'items';

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
        'category',
        'condition',
        'is_sold',
    ];

    /**
     * 型キャスト
     * boolean / int などテーブル定義に合わせておく
     */
    protected $casts = [
        'price'    => 'integer',
        'category' => 'integer',
        'condition' => 'integer',
        'is_sold'  => 'boolean',
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
        return $this->hasMany(Like::class);
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

    public function getCategoryLabelAttribute(): string
    {
        $id = (int) ($this->category ?? 0);
        return self::CATEGORY_LABELS[$id] ?? '未設定';
    }

    public function getConditionLabelAttribute(): string
    {
        $id = (int) ($this->condition ?? 0);
        return self::CONDITION_LABELS[$id] ?? '未設定';
    }

}
