<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    /**
     * 一括代入を許可するカラム
     * テーブル定義と完全一致
     */
    protected $fillable = [
        'user_id',
        'item_id',
        'body',
    ];

    /**
     * ユーザーとのリレーション
     * $comment->user でコメント投稿者取得
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 商品とのリレーション
     * $comment->item で対象商品取得
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
