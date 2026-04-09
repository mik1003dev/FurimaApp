<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemImage extends Model
{
    use HasFactory;

    // protected $table = 'item_images';

    /**
     * 一括代入を許可するカラム
     * テーブル定義と完全一致
     */
    protected $fillable = [
        'item_id',
        'path',
        'is_main',
    ];

    /**
     * 型キャスト
     */
    protected $casts = [
        'is_main' => 'boolean',
    ];

    /**
     * 親の商品（itemsテーブル）とのリレーション
     * $image->item で対応する Item を取得できる
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
