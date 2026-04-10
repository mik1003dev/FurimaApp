<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Item;

class CommentController extends Controller
{
    public function store(CommentRequest $request, Item $item_id)
    {
        $item = $item_id;

        // 出品者本人による自分の商品へのコメント投稿を防ぐ。
        if ((int) $item->user_id === (int) auth()->id()) {
            return redirect()
                ->route('items.show', $item)
                ->with('status', '自分が出品した商品にはコメントできません');
        }

        // 入力されたコメントを対象商品に紐づけて保存する。
        $item->comments()->create([
            'user_id' => auth()->id(),
            'body' => $request->input('body'),
        ]);

        // 商品詳細へ戻し、投稿完了メッセージを表示する。
        return redirect()
            ->route('items.show', $item)
            ->with('status', 'コメントを投稿しました');
    }
}
