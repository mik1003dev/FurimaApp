<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !$user->hasCompletedProfile()) {
            return redirect()
                ->route('profile.edit')
                ->with('status', '出品または購入の前にプロフィールを入力してください');
        }

        return $next($request);
    }
}
