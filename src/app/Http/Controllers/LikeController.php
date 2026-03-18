<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request, Item $item)
    {
        $userId = $request->user()->id;

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

        return back();
    }
}
