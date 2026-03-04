{{-- items/show.blade.php --}}
@extends('layouts.app')

@section('title', $item->name . '｜商品詳細')

@section('content')
<section class="item-detail">


    {{-- 上段：画像（左） / 情報（右） --}}
    <div class="item-detail__top">

        {{-- 画像 --}}
        <div class="item-detail__image">
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="item-detail__image-img">
        </div>

        {{-- 情報 --}}
        <div class="item-detail__info">

            <h1 class="item-detail__name">{{ $item->name }}</h1>

            <p class="item-detail__brand">
                {{ $item->brand ?: 'ブランドなし' }}
            </p>

            <p class="item-detail__price">
                ¥{{ number_format($item->price) }} <span class="item-detail__tax">(税込)</span>
            </p>

            {{-- いいね / コメント（未実装でも表示できるように） --}}
            <div class="item-detail__reaction">
                <div class="item-detail__reaction-item">
                    <span class="item-detail__icon">♡</span>
                    <span class="item-detail__count">
                        {{ $item->likes_count ?? 0 }}
                    </span>
                </div>
                <div class="item-detail__reaction-item">
                    <span class="item-detail__icon">💬</span>
                    <span class="item-detail__count">
                        {{ $item->comments_count ?? 0 }}
                    </span>
                </div>
            </div>

            {{-- 購入ボタン --}}
            <div class="item-detail__actions">
                <a href="{{ url('/purchase/' . $item->id) }}" class="item-detail__button item-detail__button--primary">
                    購入手続きへ
                </a>

                {{-- 未ログイン時の案内（Fortifyで login ルートが無い場合を考慮して url('/login')） --}}
                @guest
                <p class="item-detail__notice">
                    マイリスト機能を使うには <a href="{{ url('/login') }}" class="item-detail__notice-link">ログイン</a> が必要です。
                </p>
                @endguest
            </div>

            {{-- 商品説明 --}}
            <div class="item-detail__section">
                <h2 class="item-detail__section-title">商品説明</h2>
                <p class="item-detail__description">
                    {{ $item->description }}
                </p>
            </div>

            {{-- 商品情報 --}}
            <div class="item-detail__section">
                <h2 class="item-detail__section-title">商品の情報</h2>

                <dl class="item-detail__meta">
                    <div class="item-detail__meta-row">
                        <dt class="item-detail__meta-label">カテゴリー</dt>
                        <dd class="item-detail__meta-value">
                            {{-- まだカテゴリマスタが無い場合は数値表示 --}}
                            {{ $item->category_label }}
                        </dd>
                    </div>

                    <div class="item-detail__meta-row">
                        <dt class="item-detail__meta-label">商品の状態</dt>
                        <dd class="item-detail__meta-value">
                            {{ $item->condition_label }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- コメント数 --}}
            <h3 class="item-detail__comment-title">
                コメント({{ $item->comments->count() ?? 0 }})
            </h3>

            {{-- コメント一覧 --}}
            <div class="item-detail__comments">
                @forelse($item->comments as $comment)
                <div class="comment">
                    <div class="comment__header">
                        <div class="comment__avatar"></div>
                        <span class="comment__user">{{ $comment->user->name }}</span>
                    </div>
                    <div class="comment__body">
                        {{ $comment->content }}
                    </div>
                </div>
                @empty
                <p class="comment__empty">コメントはまだありません。</p>
                @endforelse
            </div>

            {{-- 投稿フォーム --}}
            <div class="item-detail__comment-form">
                <h4 class="comment-form__label">商品へのコメント</h4>

                @auth
                <form action="{{ route('comments.store', $item->id) }}" method="POST">
                    @csrf
                    <textarea
                        name="content"
                        class="comment-form__textarea"
                        placeholder="こちらにコメントが入ります。">{{ old('content') }}</textarea>

                    <button type="submit" class="comment-form__button">
                        コメントを送信する
                    </button>
                </form>
                @else
                <p class="comment__login-message">
                    コメントするには <a href="/login"   >ログイン</a> が必要です。
                </p>
                @endauth
            </div>

        </div>
    </div>
</section>
@endsection