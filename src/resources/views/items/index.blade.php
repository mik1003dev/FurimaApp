{{-- items/index.blade.php --}}
@extends('layouts.app')

@section('title', '商品一覧')

@section('content')
{{-- PG01 / PG02：商品一覧画面 --}}
<section class="item-list">
    <header class="item-list__header">
        <h1 class="item-list__title">商品一覧</h1>
    </header>

    {{-- 商品グリッド --}}
    <div class="item-list__grid">
        @forelse ($items as $item)
        <a href="{{ url('/item/' . $item->id) }}" class="item-card">
            <div class="item-card__image-wrapper">
                @if ($item->order)
                <span class="item-card__badge item-card__badge--sold">SOLD</span>
                @endif
                <img
                    src="{{ $item->image_url }}"
                    alt="{{ $item->name }}"
                    class="item-card__image">
            </div>

            <div class="item-card__body">
                <h2 class="item-card__name">{{ $item->name }}</h2>
            </div>
        </a>
        @empty
        <p class="item-list__empty">
            @if (($activeTab ?? 'all') === 'mylist')
            @guest
            マイリストを見るには <a href="{{ url('/login') }}">ログイン</a> が必要です。
            @else
            いいねした商品がありません。
            @endguest
            @else
            商品がありません。
            @endif
        </p>
        @endforelse
    </div>

    {{-- ページネーション --}}
    @if (method_exists($items, 'links'))
    <div class="item-list__pagination">
        {{ $items->withQueryString()->links() }}
    </div>
    @endif
</section>
@endsection
