<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * PG01 / PG02：商品一覧画面
     *
     * - /              … 通常一覧
     * - /?tab=mylist   … マイリストタブ（現時点では中身は同じ）
     *
     * この段階ではまだ検索・マイリストの絞り込みは入れず、
     * 「DBから商品を取得して一覧に渡す」ところまでにします。
     */
    public function index(Request $request)
    {
        // どのタブがアクティブか（view 側のタブ表示用）
        $activeTab = $request->tab === 'mylist' ? 'mylist' : 'all';

        // 商品一覧を新着順で取得
        // seller / mainImage は Itemモデルで定義したリレーション
        $items = Item::with(['seller', 'mainImage'])
            ->orderByDesc('created_at')
            ->get();

        // items/index.blade.php にデータを渡す
        return view('items.index', [
            'items'     => $items,
            'activeTab' => $activeTab,
        ]);
    }

    // 商品詳細 PG03 / PG04
    public function show(Item $item)
    {
        $item->load(['seller', 'images']); // モデルの定義に合わせて調整

        return view('items.show', [
            'item' => $item,
        ]);
    }
}