@extends('layouts.app')

@section('title', '商品の出品')

@section('content')
<section class="item-create">
    @php
        $selectedCategoryCodes = collect(old('category_codes', []))
            ->map(fn ($value) => (int) $value)
            ->all();
        $currentUploadedImagePath = old('uploaded_item_image', $uploadedImagePath);
    @endphp

    <h1 class="item-create__title">商品の出品</h1>

    @if (session('status'))
        <div class="item-create__status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="item-create__error">
            <p class="item-create__error-line">入力内容に不備があります。</p>
        </div>
    @endif

    <form action="{{ route('items.storeImage') }}" method="POST" enctype="multipart/form-data" class="item-create__form" novalidate>
        @csrf
        <section class="item-create__section">
            <h2 class="item-create__section-title item-create__section-title--plain">商品画像</h2>
            <div id="item-image-picker" class="item-create__image-picker {{ !empty($currentUploadedImagePath) ? 'is-has-image' : '' }}">
                <label for="item_image" class="item-create__image-box">
                    @if (!empty($currentUploadedImagePath))
                        <img id="item-image-preview" src="{{ asset('storage/' . $currentUploadedImagePath) }}" alt="商品画像プレビュー" class="item-create__image-preview">
                    @else
                        <img id="item-image-preview" alt="商品画像プレビュー" class="item-create__image-preview" style="display: none;">
                    @endif
                    <button
                        type="button"
                        id="item-image-remove"
                        class="item-create__image-remove {{ !empty($currentUploadedImagePath) ? '' : 'item-create__image-remove--hidden' }}"
                        aria-label="商品画像を削除する">
                        ×
                    </button>
                    <span id="item-image-trigger" class="item-create__image-action">
                        {{ !empty($currentUploadedImagePath) ? '画像を変更する' : '画像を選択する' }}
                    </span>
                </label>
                <input id="item_image" type="file" name="item_image" accept="image/png,image/jpeg" class="item-create__image-input">
                <input id="uploaded_item_image" type="hidden" name="uploaded_item_image" value="{{ $currentUploadedImagePath }}">
            </div>
            @error('item_image')
                <p class="item-create__field-error">{{ $message }}</p>
            @enderror
        </section>

        <section class="item-create__section">
            <h2 class="item-create__section-title">商品の詳細</h2>

            <div class="item-create__group">
                <label class="item-create__label">カテゴリー</label>
                <div class="item-create__chips">
                    @foreach ($categories as $code => $label)
                        <input
                            id="category_{{ $code }}"
                            type="checkbox"
                            name="category_codes[]"
                            value="{{ $code }}"
                            class="item-create__category-input"
                            {{ in_array((int) $code, $selectedCategoryCodes, true) ? 'checked' : '' }}>
                        <label for="category_{{ $code }}" class="item-create__chip">{{ $label }}</label>
                    @endforeach
                </div>
                @error('category_codes')
                    <p class="item-create__field-error">{{ $message }}</p>
                @enderror
                @error('category_codes.*')
                    <p class="item-create__field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="item-create__group">
                <label for="condition" class="item-create__label">商品の状態</label>
                <div class="item-create__select-wrap">
                    <select id="condition" name="condition" class="item-create__select">
                        <option value="" {{ old('condition') ? '' : 'selected' }} disabled>選択してください</option>
                        @foreach ($conditions as $code => $label)
                            <option value="{{ $code }}" {{ (string) old('condition') === (string) $code ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('condition')
                    <p class="item-create__field-error">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <section class="item-create__section">
            <h2 class="item-create__section-title">商品名と説明</h2>

            <div class="item-create__group">
                <label for="name" class="item-create__label">商品名</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" class="item-create__input">
                @error('name')
                    <p class="item-create__field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="item-create__group">
                <label for="brand" class="item-create__label">ブランド名</label>
                <input id="brand" name="brand" type="text" value="{{ old('brand') }}" class="item-create__input">
                @error('brand')
                    <p class="item-create__field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="item-create__group">
                <label for="description" class="item-create__label">商品の説明</label>
                <textarea id="description" name="description" class="item-create__textarea">{{ old('description') }}</textarea>
                @error('description')
                    <p class="item-create__field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="item-create__group">
                <label for="price" class="item-create__label">販売価格</label>
                <div class="item-create__price-wrap">
                    <span class="item-create__price-mark">¥</span>
                    <input id="price" name="price" type="text" value="{{ old('price') }}" class="item-create__input item-create__input--price">
                </div>
                @error('price')
                    <p class="item-create__field-error">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <button type="submit" class="item-create__submit">出品する</button>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('item_image');
    const preview = document.getElementById('item-image-preview');
    const picker = document.getElementById('item-image-picker');
    const trigger = document.getElementById('item-image-trigger');
    const removeButton = document.getElementById('item-image-remove');
    const uploadedImageInput = document.getElementById('uploaded_item_image');

    if (!input || !preview || !picker || !trigger || !removeButton || !uploadedImageInput) {
        return;
    }

    let currentObjectUrl = null;

    const setState = function (hasImage) {
        picker.classList.toggle('is-has-image', hasImage);
        removeButton.classList.toggle('item-create__image-remove--hidden', !hasImage);
        trigger.textContent = hasImage ? '画像を変更する' : '画像を選択する';
    };

    const clearImage = function () {
        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = null;
        }

        preview.removeAttribute('src');
        preview.style.display = 'none';
        input.value = '';
        uploadedImageInput.value = '';
        setState(false);
    };

    setState(!!preview.getAttribute('src'));

    input.addEventListener('change', function (event) {
        const file = event.target.files && event.target.files[0];
        if (!file || !file.type.startsWith('image/')) {
            return;
        }

        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
        }

        currentObjectUrl = URL.createObjectURL(file);
        preview.src = currentObjectUrl;
        preview.style.display = 'block';
        setState(true);
    });

    removeButton.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        clearImage();
    });
});
</script>
@endsection
