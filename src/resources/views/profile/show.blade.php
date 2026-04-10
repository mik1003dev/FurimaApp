@extends('layouts.app')

@section('title', 'プロフィール')

@section('content')
@php
    $avatarPath = !empty($user->avatar_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar_path)
        ? $user->avatar_path
        : null;
@endphp
<section class="profile-page">
    <div class="profile-page__header">
        <div class="profile-page__identity">
            <img
                src="{{ $avatarPath ? asset('storage/' . $avatarPath) : asset('images/default-avatar.png') }}"
                alt="{{ $user->name }}"
                class="profile-page__avatar-image">

            <h1 class="profile-page__name">{{ $user->name }}</h1>
        </div>

        <a href="{{ route('profile.edit') }}" class="profile-page__edit-button">プロフィールを編集</a>
    </div>

    <div class="profile-page__tabs">
        <a href="{{ route('profile.show') }}" class="profile-page__tab {{ $activeTab === 'sell' ? 'is-active' : '' }}">
            出品した商品
        </a>
        <a href="{{ route('profile.show', ['page' => 'buy']) }}" class="profile-page__tab {{ $activeTab === 'buy' ? 'is-active' : '' }}">
            購入した商品
        </a>
    </div>

    @php
        $items = $activeTab === 'buy' ? $purchasedItems : $sellingItems;
    @endphp

    <div class="profile-page__grid">
        @forelse ($items as $item)
            <a href="{{ route('items.show', $item) }}" class="item-card profile-page__item-card">
                <div class="item-card__image-wrapper profile-page__item-image-wrapper">
                    @if ($activeTab === 'sell' && $item->order)
                        <span class="item-card__badge item-card__badge--sold">SOLD</span>
                    @endif

                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="item-card__image">
                </div>

                <div class="item-card__body">
                    <h2 class="item-card__name">{{ $item->name }}</h2>
                </div>
            </a>
        @empty
            <p class="profile-page__empty">
                {{ $activeTab === 'buy' ? '購入した商品がありません。' : '出品した商品がありません。' }}
            </p>
        @endforelse
    </div>
</section>
@endsection
