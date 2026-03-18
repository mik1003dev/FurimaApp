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
        $user = $request->user()->load([
            'items.mainImage',
            'items.order',
            'purchasedItems.mainImage',
            'purchasedItems.order',
        ]);

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

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_path'] = $path;

            if (!empty($tempAvatarPath) && Storage::disk('public')->exists($tempAvatarPath)) {
                Storage::disk('public')->delete($tempAvatarPath);
            }
        } elseif (!empty($validated['avatar_temp_path'])) {
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

        if (!empty($validated['avatar_path']) && !empty($oldAvatarPath) && $oldAvatarPath !== $validated['avatar_path'] && Storage::disk('public')->exists($oldAvatarPath)) {
            Storage::disk('public')->delete($oldAvatarPath);
        }

        if (
            !empty($validated['avatar_path'] ?? $user->avatar_path)
            && !empty($validated['postal_code'] ?? $user->postal_code)
            && !empty($validated['address'] ?? $user->address)
            && empty($user->profile_completed_at)
        ) {
            $validated['profile_completed_at'] = now();
        }

        $user->update($validated);

        return redirect()
            ->route('profile.show')
            ->with('status', 'プロフィールを更新しました');
    }
}
