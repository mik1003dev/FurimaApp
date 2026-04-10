@extends('layouts.app')

@section('title', 'プロフィール設定')

@section('content')
@php
    $avatarTempPath = old('avatar_temp_path');
    $storedAvatarPath = !empty($user->avatar_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar_path)
        ? $user->avatar_path
        : null;
    $avatarPreviewUrl = $avatarTempPath
        ? asset('storage/' . $avatarTempPath)
        : ($storedAvatarPath ? asset('storage/' . $storedAvatarPath) : asset('images/default-avatar.png'));
@endphp
<section class="profile-edit">
    <h1 class="profile-edit__title">プロフィール設定</h1>

    @if (session('status'))
        <div class="profile-edit__status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="profile-edit__error">
            <p class="profile-edit__error-line">入力内容に不備があります。</p>
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-edit__form" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="avatar_temp_path" value="{{ $avatarTempPath }}">

        <div class="profile-edit__avatar-row">
            <div class="profile-edit__avatar-stack">
                <div class="profile-edit__avatar-wrap">
                    <img id="avatar-preview" src="{{ $avatarPreviewUrl }}" alt="プロフィール画像" class="profile-edit__avatar-image">
                </div>
                <label for="avatar" class="profile-edit__avatar-button">画像を選択する</label>
            </div>
            <input id="avatar" type="file" name="avatar" accept="image/png,image/jpeg" class="profile-edit__avatar-input">
            @error('avatar')
                <p class="profile-edit__field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="profile-edit__group">
            <label for="name" class="profile-edit__label">ユーザー名</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="profile-edit__input" aria-invalid="@error('name')true @else false @enderror">
            @error('name')
                <p class="profile-edit__field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="profile-edit__group">
            <label for="postal_code" class="profile-edit__label">郵便番号</label>
            <input id="postal_code" type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" class="profile-edit__input" aria-invalid="@error('postal_code')true @else false @enderror">
            @error('postal_code')
                <p class="profile-edit__field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="profile-edit__group">
            <label for="address" class="profile-edit__label">住所</label>
            <input id="address" type="text" name="address" value="{{ old('address', $user->address) }}" class="profile-edit__input" aria-invalid="@error('address')true @else false @enderror">
            @error('address')
                <p class="profile-edit__field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="profile-edit__group">
            <label for="building" class="profile-edit__label">建物名</label>
            <input id="building" type="text" name="building" value="{{ old('building', $user->building) }}" class="profile-edit__input">
            @error('building')
                <p class="profile-edit__field-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="profile-edit__submit">更新する</button>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('avatar');
    const preview = document.getElementById('avatar-preview');

    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', function (event) {
        const file = event.target.files && event.target.files[0];
        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            return;
        }

        preview.src = URL.createObjectURL(file);
    });
});
</script>
@endsection
