<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        // 出品商品・購入商品をまとめて読み込み、プロフィール画面の表示に使う。
        $user = $request->user()->load([
            'items.mainImage',
            'items.order',
            'purchasedItems.mainImage',
            'purchasedItems.order',
        ]);

        // クエリパラメータに応じて出品一覧か購入一覧のタブを切り替える。
        $activeTab = $request->query('page') === 'buy' ? 'buy' : 'sell';

        return view('profile.show', [
            'user' => $user,
            'activeTab' => $activeTab,
            'sellingItems' => $user->items->sortByDesc('created_at')->values(),
            'purchasedItems' => $user->purchasedItems
                ->sortByDesc(fn ($item) => optional($item->pivot)->created_at)
                ->values(),
        ]);
    }

    public function edit(Request $request)
    {
        // 現在のユーザー情報を編集画面へ渡す。
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileRequest $request)
    {
        $validated = $request->validated();

        $user = $request->user();
        $oldAvatarPath = $user->avatar_path;
        $tempAvatarPath = $validated['avatar_temp_path'] ?? null;

        // 新しい画像がアップロードされた場合は、その画像を本保存として採用する。
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_path'] = $path;

            // 一時保存済みの画像があれば不要になるため削除する。
            if (!empty($tempAvatarPath) && Storage::disk('public')->exists($tempAvatarPath)) {
                Storage::disk('public')->delete($tempAvatarPath);
            }
        } elseif (!empty($validated['avatar_temp_path'])) {
            // 再入力時に保持していた一時画像を正式な保存先へ移動する。
            $tempPath = $validated['avatar_temp_path'];
            $extension = pathinfo($tempPath, PATHINFO_EXTENSION);
            $finalPath = 'avatars/' . Str::uuid() . ($extension ? '.' . $extension : '');

            if (Storage::disk('public')->exists($tempPath)) {
                Storage::disk('public')->move($tempPath, $finalPath);
                $validated['avatar_path'] = $finalPath;
            }
        }

        unset($validated['avatar']);
        unset($validated['avatar_temp_path']);

        // アバターが差し替わった場合のみ、古い画像を削除する。
        if (!empty($validated['avatar_path']) && !empty($oldAvatarPath) && $oldAvatarPath !== $validated['avatar_path'] && Storage::disk('public')->exists($oldAvatarPath)) {
            Storage::disk('public')->delete($oldAvatarPath);
        }

        // 必須プロフィール項目が初めてそろったタイミングを記録する。
        if (
            !empty($validated['avatar_path'] ?? $user->avatar_path)
            && !empty($validated['postal_code'] ?? $user->postal_code)
            && !empty($validated['address'] ?? $user->address)
            && empty($user->profile_completed_at)
        ) {
            $validated['profile_completed_at'] = now();
        }

        // バリデーション済みの内容でプロフィールを更新する。
        $user->update($validated);

        return redirect()
            ->route('profile.show')
            ->with('status', 'プロフィールを更新しました');
    }
}
