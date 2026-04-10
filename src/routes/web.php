<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;

// 商品一覧トップページ
Route::get('/', [ItemController::class, 'index'])->name('items.index');

// 商品詳細ページ
Route::get('/item/{item_id}', [ItemController::class, 'show'])
    ->name('items.show');

// 商品出品ページ表示
// ログイン済みかつメール認証済み、プロフィール入力済みユーザーのみ利用可能
Route::get('/sell', [ItemController::class, 'create'])
    ->name('items.create')
    ->middleware(['auth', 'verified', 'profile.completed']);

// 商品出品処理
// 画像アップロードを含めて商品情報を保存する
Route::post('/sell', [ItemController::class, 'storeImage'])
    ->name('items.storeImage')
    ->middleware(['auth', 'verified', 'profile.completed']);

// いいねの追加・解除
Route::post('/item/{item_id}/like', [LikeController::class, 'toggle'])
    ->name('likes.toggle')
    ->middleware(['auth', 'verified']);

// 商品へのコメント投稿
Route::post('/item/{item_id}/comments', [CommentController::class, 'store'])
    ->name('comments.store')
    ->middleware(['auth', 'verified']);

// 商品購入画面
// ログイン済みかつメール認証済み、プロフィール入力済みユーザーのみ利用可能
Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])
    ->name('purchase.show')
    ->middleware(['auth', 'verified', 'profile.completed']);

// 配送先住所変更画面
Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])
    ->name('purchase.address.edit')
    ->middleware(['auth', 'verified', 'profile.completed']);

// 配送先住所更新処理
Route::put('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])
    ->name('purchase.address.update')
    ->middleware(['auth', 'verified', 'profile.completed']);

// Stripe Checkout セッション作成と購入開始処理
Route::post('/purchase/{item_id}', [PurchaseController::class, 'purchase'])
    ->name('purchase.purchase')
    ->middleware(['auth', 'verified', 'profile.completed']);

// 購入完了後の反映処理
// Stripe から戻った後に注文データを保存する
Route::get('/purchase/{item_id}/complete', [PurchaseController::class, 'complete'])
    ->name('purchase.complete')
    ->middleware(['auth', 'verified', 'profile.completed']);

// マイページ関連
// ログイン済みかつメール認証済みユーザーのみ利用可能
Route::middleware(['auth', 'verified'])->group(function () {
    // プロフィール表示画面
    Route::get('/mypage', [ProfileController::class, 'show'])
        ->name('profile.show');

    // プロフィール編集画面
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    // プロフィール更新処理
    Route::put('/mypage/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
});
