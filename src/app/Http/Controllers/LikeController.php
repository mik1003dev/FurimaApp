<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request, Item $item_id)
    {
        $item = $item_id;
        $userId = $request->user()->id;

        // 出品者本人による自分の商品へのいいねを防ぐ。
        if ((int) $item->user_id === (int) $userId) {
            return back()->with('status', '自分が出品した商品にはいいねできません');
        }

        // 既存のいいねがあれば解除し、なければ新規登録する。
        $existing = Like::where('user_id', $userId)
            ->where('item_id', $item->id)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            Like::create([
                'user_id' => $userId,
                'item_id' => $item->id,
            ]);
        }

        // 元の画面へ戻して表示内容を再読み込みさせる。
        return back();
    }
}
