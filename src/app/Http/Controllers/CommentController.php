<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Item;

class CommentController extends Controller
{
    public function store(CommentRequest $request, Item $item)
    {
        $item->comments()->create([
            'user_id' => auth()->id(),
            'body' => $request->input('body'),
        ]);

        return redirect()
            ->route('items.show', $item)
            ->with('status', 'コメントを投稿しました');
    }
}
