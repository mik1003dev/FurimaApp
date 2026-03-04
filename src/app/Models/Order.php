<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * 一括代入を許可するカラム
     * テーブル定義と完全一致
     */
    protected $fillable = [
        'user_id',
        'item_id',
        'price',
        'payment_method',
        'shipping_postal_code',
        'shipping_address',
        'shipping_building',
        'status',
    ];

    /**
     * 型キャスト
     */
    protected $casts = [
        'price'          => 'integer',
        'payment_method' => 'integer',
        'status'         => 'integer',
    ];

    /**
     * 注文者（usersテーブル）とのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 商品（itemsテーブル）とのリレーション
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
