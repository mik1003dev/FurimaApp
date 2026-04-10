<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionRequest;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function create()
    {
        // 出品フォームで使用する選択肢と、セッションに保持した画像パスを渡す。
        return view('items.create', [
            'categories' => Item::CATEGORY_LABELS,
            'conditions' => Item::CONDITION_LABELS,
            'uploadedImagePath' => session('uploaded_item_image'),
        ]);
    }

    public function storeImage(ExhibitionRequest $request)
    {
        $validated = $request->validated();
        $uploadedImagePath = $validated['uploaded_item_image'] ?? null;

        // 新規画像があるときは保存後のパスを使い、なければ hidden の既存パスを再利用する。
        if ($request->hasFile('item_image')) {
            $uploadedImagePath = $request->file('item_image')->store('items', 'public');
        }
        $categoryCodes = collect($validated['category_codes'])
            ->map(fn ($code) => (int) $code)
            ->unique()
            ->values();

        // 商品本体・メイン画像・カテゴリ紐づけをまとめて登録する。
        DB::transaction(function () use ($validated, $uploadedImagePath, $categoryCodes) {
            $item = Item::create([
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'brand' => $validated['brand'] ?? null,
                'description' => $validated['description'],
                'price' => (int) $validated['price'],
                'condition' => (int) $validated['condition'],
            ]);

            ItemImage::create([
                'item_id' => $item->id,
                'path' => $uploadedImagePath,
                'is_main' => true,
            ]);

            $rows = $categoryCodes->map(function ($code) use ($item) {
                return [
                    'item_id' => $item->id,
                    'category_code' => $code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            if (!empty($rows)) {
                ItemCategory::insert($rows);
            }
        });

        return redirect()
            ->route('items.index');
    }

    public function index(Request $request)
    {
        // メール未認証ユーザーは一覧表示前に認証案内へ誘導する。
        if (auth()->check() && ! auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // どのタブがアクティブか（view 側のタブ表示用）
        $activeTab = $request->tab === 'mylist' ? 'mylist' : 'all';
        $keyword = trim((string) $request->input('keyword', ''));

        $query = Item::with(['seller', 'mainImage', 'itemCategories', 'order'])
            ->withCount('likes')
            ->orderByDesc('created_at');

        // 自分が出品した商品は一覧に表示しない。
        if (auth()->check()) {
            $query->where('items.user_id', '!=', auth()->id());
        }

        // 商品名の部分一致検索
        if ($keyword !== '') {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        // マイリスト：ログインユーザーがいいねした商品だけ
        if ($activeTab === 'mylist') {
            if (auth()->check()) {
                $query->select('items.*')
                    ->join('likes', function ($join) {
                        $join->on('likes.item_id', '=', 'items.id')
                            ->where('likes.user_id', '=', auth()->id());
                    })
                    ->orderByDesc('likes.created_at');
            } else {
                // 未ログインで mylist を開いたら空にする
                $query->whereRaw('1 = 0');
            }
        }

        // 条件に合う商品一覧を取得して画面に渡す。
        $items = $query->get();

        return view('items.index', [
            'items'     => $items,
            'activeTab' => $activeTab,
        ]);
    }

    // 商品詳細 PG03 / PG04
    public function show(Item $item_id)
    {
        $item = $item_id;

        // 商品詳細表示に必要な関連データと件数をまとめて読み込む。
        $item->load([
            'seller',
            'images',
            'itemCategories',
            'order',
            'comments' => function ($query) {
                $query->latest()->with('user');
            },
        ])
            ->loadCount(['likes', 'comments']);

        // ログイン中ユーザーがこの商品にいいね済みか判定する。
        $isLikedByCurrentUser = auth()->check()
            ? $item->likes()->where('user_id', auth()->id())->exists()
            : false;

        return view('items.show', [
            'item' => $item,
            'isLikedByCurrentUser' => $isLikedByCurrentUser,
        ]);
    }
}
