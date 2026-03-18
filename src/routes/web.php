<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [ItemController::class, 'index'])->name('items.index');

Route::get('/item/{item}', [ItemController::class, 'show'])
    ->name('items.show');

Route::get('/sell', [ItemController::class, 'create'])
    ->name('items.create')
    ->middleware(['auth', 'profile.completed']);

Route::post('/sell', [ItemController::class, 'storeImage'])
    ->name('items.storeImage')
    ->middleware(['auth', 'profile.completed']);

Route::post('/item/{item}/like', [LikeController::class, 'toggle'])
    ->name('likes.toggle')
    ->middleware('auth');

Route::post('/item/{item}/comments', [CommentController::class, 'store'])
    ->name('comments.store')
    ->middleware('auth');

Route::get('/purchase/{item}', [PurchaseController::class, 'show'])
    ->name('purchase.show')
    ->middleware(['auth', 'profile.completed']);

Route::get('/purchase/address/{item}', [PurchaseController::class, 'editAddress'])
    ->name('purchase.address.edit')
    ->middleware(['auth', 'profile.completed']);

Route::put('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])
    ->name('purchase.address.update')
    ->middleware(['auth', 'profile.completed']);

Route::post('/purchase/{item}', [PurchaseController::class, 'purchase'])
    ->name('purchase.purchase')
    ->middleware(['auth', 'profile.completed']);

Route::get('/purchase/{item}/complete', [PurchaseController::class, 'complete'])
    ->name('purchase.complete')
    ->middleware(['auth', 'profile.completed']);

Route::middleware('auth')->group(function () {
    Route::get('/mypage', [ProfileController::class, 'show'])
        ->name('profile.show');
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
});
