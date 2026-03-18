{{-- items/show.blade.php --}}
@extends('layouts.app')

@section('title', $item->name . '｜商品詳細')

@section('content')
<section class="item-detail">
    @php
        $isSold = (bool) $item->order;
    @endphp

    <div class="item-detail__top">
        <div class="item-detail__image">
            @if ($isSold)
            <span class="item-card__badge item-card__badge--sold">SOLD</span>
            @endif
            <img
                src="{{ $item->image_url }}"
                alt="{{ $item->name }}"
                class="item-detail__image-img">
        </div>

        <div class="item-detail__info">
            <h1 class="item-detail__name">{{ $item->name }}</h1>

            <p class="item-detail__brand">
                {{ $item->brand ?: 'ブランドなし' }}
            </p>

            <p class="item-detail__price">
                <span class="item-detail__price-symbol">¥</span>{{ number_format($item->price) }} <span class="item-detail__tax">(税込)</span>
            </p>

            <div class="item-detail__reaction">
                {{-- いいね --}}
                <form action="{{ route('likes.toggle', $item->id) }}" method="POST" class="item-detail__reaction-item">
                    @csrf
                    <button type="submit" class="item-detail__icon-button" aria-label="いいね">
                        <img src="{{ asset('images/ハートロゴ_デフォルト.png') }}" alt="いいね" class="item-detail__icon-image">
                    </button>
                    <span class="item-detail__count">{{ $item->likes_count ?? 0 }}</span>
                </form>

                {{-- コメント --}}
                <div class="item-detail__reaction-item">
                    <img src="{{ asset('images/ふきだしロゴ.png') }}" alt="コメント" class="item-detail__icon-image">
                    <span class="item-detail__count">
                        {{ $item->comments_count ?? 0 }}
                    </span>
                </div>
            </div>

            <div class="item-detail__actions">
                @if ($isSold)
                <button type="button" class="item-detail__button item-detail__button--primary item-detail__button--disabled" disabled>
                    購入手続きへ
                </button>
                @else
                <a href="{{ route('purchase.show', $item->id) }}" class="item-detail__button item-detail__button--primary">
                    購入手続きへ
                </a>
                @endif
            </div>

            <div class="item-detail__section">
                <h2 class="item-detail__section-title">商品説明</h2>
                <p class="item-detail__description">{{ $item->description }}</p>
            </div>

            <div class="item-detail__section">
                <h2 class="item-detail__section-title">商品の情報</h2>

                <dl class="item-detail__meta">
                    <div class="item-detail__meta-row">
                        <dt class="item-detail__meta-label">カテゴリー</dt>
                        <dd class="item-detail__meta-value item-detail__meta-value--categories">
                            @php
                            $labels = !empty($item->category_labels)
                            ? $item->category_labels
                            : [($item->category_label ?? '未設定')];
                            @endphp

                            <div class="item-detail__category-list">
                                @foreach ($labels as $label)
                                <span class="item-detail__category-pill">{{ $label }}</span>
                                @endforeach
                            </div>
                        </dd>
                    </div>

                    <div class="item-detail__meta-row">
                        <dt class="item-detail__meta-label">商品の状態</dt>
                        <dd class="item-detail__meta-value item-detail__meta-value--status">{{ $item->condition_label }}</dd>
                    </div>
                </dl>
            </div>

            <h3 class="item-detail__comment-title">コメント({{ $item->comments->count() }})</h3>

            <div class="item-detail__comments">
                @forelse($item->comments as $comment)
                <div class="comment">
                    <div class="comment__header">
                        <div class="comment__avatar"></div>
                        <span class="comment__user">{{ $comment->user->name ?? 'ユーザー' }}</span>
                    </div>
                    <div class="comment__body">{{ $comment->body }}</div>
                </div>
                @empty
                <p class="comment__empty">コメントはまだありません。</p>
                @endforelse
            </div>

            <div class="item-detail__comment-form">
                <h4 class="comment-form__label">商品へのコメント</h4>

                <form action="{{ route('comments.store', $item->id) }}" method="POST">
                    @csrf
                    <textarea
                        name="body"
                        class="comment-form__textarea"
                        placeholder="{{ $isSold ? '売り切れのためコメントできません。' : 'こちらにコメントが入ります。' }}"
                        {{ $isSold ? 'disabled' : '' }}>{{ old('body') }}</textarea>

                    @error('body')
                    <p class="item-detail__error">{{ $message }}</p>
                    @enderror

                    @if ($isSold)
                    <button type="submit" class="comment-form__button" disabled>
                        コメントを送信する
                    </button>
                    @else
                    <button type="submit" class="comment-form__button">
                        コメントを送信する
                    </button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
