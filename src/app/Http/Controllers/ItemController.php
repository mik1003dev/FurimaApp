<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function create()
    {
        return view('items.create', [
            'categories' => Item::CATEGORY_LABELS,
            'conditions' => Item::CONDITION_LABELS,
            'uploadedImagePath' => session('uploaded_item_image'),
        ]);
    }

    public function storeImage(Request $request)
    {
        $uploadedImagePath = $request->input('uploaded_item_image');

        // 新規に画像が選択されている場合は先に検証して一時保存する。
        // カテゴリーなど他項目のエラー時にも hidden で再表示できるようにするため。
        if ($request->hasFile('item_image')) {
            $request->validate(
                [
                    'item_image' => ['image', 'mimes:jpeg,png,jpg', 'max:2048'],
                ],
                [
                    'item_image.image' => '商品画像は画像ファイルを選択してください',
                    'item_image.mimes' => '商品画像はjpegまたはpng形式で選択してください',
                    'item_image.max' => '商品画像は2MB以下で選択してください',
                ]
            );

            $uploadedImagePath = $request->file('item_image')->store('items', 'public');
        }

        $request->merge([
            'uploaded_item_image' => $uploadedImagePath,
        ]);

        $request->validate(
            [
                'uploaded_item_image' => ['required'],
                'category_codes' => ['required'],
                'name' => ['required'],
                'description' => ['required', 'max:255'],
                'condition' => ['required'],
                'price' => ['required', 'numeric', 'min:0'],
                'brand' => ['nullable', 'string', 'max:255'],
            ],
            [
                'uploaded_item_image.required' => '商品画像を選択してください',
                'category_codes.required' => 'カテゴリーを1つ以上選択してください',
                'name.required' => '商品名を入力してください',
                'description.required' => '商品の説明を入力してください',
                'description.max' => '商品の説明は255文字以内で入力してください',
                'condition.required' => '商品の状態を選択してください',
                'price.required' => '販売価格を入力してください',
                'price.numeric' => '販売価格は数値で入力してください',
                'price.min' => '販売価格は0円以上で入力してください',
                'brand.max' => 'ブランド名は255文字以内で入力してください',
            ]
        );

        $categoryCodes = collect((array) $request->input('category_codes', []))
            ->map(fn ($code) => (int) $code)
            ->filter(fn ($code) => isset(Item::CATEGORY_LABELS[$code]))
            ->unique()
            ->values();

        DB::transaction(function () use ($request, $uploadedImagePath, $categoryCodes) {
            $item = Item::create([
                'user_id' => auth()->id(),
                'name' => $request->input('name'),
                'brand' => $request->input('brand'),
                'description' => $request->input('description'),
                'price' => (int) $request->input('price'),
                'condition' => (int) $request->input('condition'),
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
        // どのタブがアクティブか（view 側のタブ表示用）
        $activeTab = $request->tab === 'mylist' ? 'mylist' : 'all';
        $keyword = trim((string) $request->input('keyword', ''));

        $query = Item::with(['seller', 'mainImage', 'itemCategories', 'order'])
            ->withCount('likes')
            ->orderByDesc('created_at');

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

        $items = $query->get();

        return view('items.index', [
            'items'     => $items,
            'activeTab' => $activeTab,
        ]);
    }

    // 商品詳細 PG03 / PG04
    public function show(Item $item)
    {
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

        return view('items.show', [
            'item' => $item,
        ]);
    }
}
