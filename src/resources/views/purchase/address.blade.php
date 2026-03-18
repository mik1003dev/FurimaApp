@extends('layouts.app')

@section('title', '送付先住所変更')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endpush

@section('content')
<section class="purchase-address">
    <h1 class="purchase-address__title">住所の変更</h1>

    @if ($errors->any())
    <div class="purchase-address__error">
        <p class="purchase-address__error-line">入力内容に不備があります。</p>
    </div>
    @endif

    <form action="{{ route('purchase.address.update', $item->id) }}" method="POST" class="purchase-address__form" novalidate>
        @csrf
        @method('PUT')

        <div class="purchase-address__group">
            <label for="postal_code" class="purchase-address__label">郵便番号</label>
            <input
                id="postal_code"
                type="text"
                name="postal_code"
                value="{{ old('postal_code', $shippingAddress['postal_code']) }}"
                class="purchase-address__input"
                placeholder="123-4567">
            @error('postal_code')
            <p class="purchase-address__field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="purchase-address__group">
            <label for="address" class="purchase-address__label">住所</label>
            <input
                id="address"
                type="text"
                name="address"
                value="{{ old('address', $shippingAddress['address']) }}"
                class="purchase-address__input">
            @error('address')
            <p class="purchase-address__field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="purchase-address__group">
            <label for="building" class="purchase-address__label">建物名</label>
            <input
                id="building"
                type="text"
                name="building"
                value="{{ old('building', $shippingAddress['building']) }}"
                class="purchase-address__input">
            @error('building')
            <p class="purchase-address__field-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="purchase-address__submit">更新する</button>
    </form>
</section>
@endsection
