@extends('layouts.app')

@section('title', '商品購入')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endpush

@section('content')
<section class="purchase-page">
    <form action="{{ route('purchase.purchase', $item->id) }}" method="POST" class="purchase-page__layout" novalidate target="_blank">
        @csrf
        <div class="purchase-page__left">
            <div class="purchase-item">
                <div class="purchase-item__image-wrap">
                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="purchase-item__image">
                </div>
                <div class="purchase-item__meta">
                    <h1 class="purchase-item__name">{{ $item->name }}</h1>
                    <p class="purchase-item__price">¥ {{ number_format($item->price) }}</p>
                </div>
            </div>

            <div class="purchase-block purchase-block--payment">
                <h2 class="purchase-block__title">支払い方法</h2>
                <select class="purchase-block__select" name="payment_method" id="payment-method-select" aria-describedby="payment-method-error" required>
                    <option value="" {{ old('payment_method') ? '' : 'selected' }} disabled>選択してください</option>
                    <option value="convenience" {{ old('payment_method') === 'convenience' ? 'selected' : '' }}>コンビニ払い</option>
                    <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>カード払い</option>
                </select>
                <p class="purchase-block__error" id="payment-method-error" aria-live="polite">
                    @error('payment_method')
                    {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="purchase-block purchase-block--shipping">
                <div class="purchase-block__heading">
                    <h2 class="purchase-block__title">配送先</h2>
                    <a href="{{ route('purchase.address.edit', $item->id) }}" class="purchase-block__change">変更する</a>
                </div>
                @php
                $postal = $shippingAddress['postal_code'] ?? 'XXX-YYYY';
                $addressLine = trim(($shippingAddress['address'] ?? '') . ' ' . ($shippingAddress['building'] ?? ''));
                @endphp
                <p class="purchase-block__address">
                    〒 {{ $postal }}<br>
                    {{ $addressLine !== '' ? $addressLine : 'ここには住所と建物が入ります' }}
                </p>
            </div>
        </div>

        <aside class="purchase-page__right">
            <div class="purchase-summary">
                <div class="purchase-summary__row">
                    <span class="purchase-summary__label">商品代金</span>
                    <span class="purchase-summary__value">¥ {{ number_format($item->price) }}</span>
                </div>
                <div class="purchase-summary__row">
                    <span class="purchase-summary__label">支払い方法</span>
                    <span class="purchase-summary__value" id="payment-method-summary">未選択</span>
                </div>
            </div>

            @if ($item->order)
            <button type="button" class="purchase-page__button" disabled>SOLD</button>
            @else
            <button type="submit" class="purchase-page__button" id="purchase-button">購入する</button>
            <p class="purchase-page__note">決済画面は別タブで開きます。</p>
            @endif
        </aside>
    </form>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethodSelect = document.getElementById('payment-method-select');
        const paymentMethodSummary = document.getElementById('payment-method-summary');
        const paymentMethodError = document.getElementById('payment-method-error');

        if (!paymentMethodSelect || !paymentMethodSummary || !paymentMethodError) {
            return;
        }

        const setError = (message) => {
            paymentMethodError.textContent = message;
        };

        const syncPaymentMethod = () => {
            const selectedOption = paymentMethodSelect.options[paymentMethodSelect.selectedIndex];
            const selectedValue = paymentMethodSelect.value;

            if (!selectedValue || !selectedOption) {
                paymentMethodSummary.textContent = '未選択';
                return false;
            }

            paymentMethodSummary.textContent = selectedOption.text;
            setError('');
            return true;
        };

        paymentMethodSelect.addEventListener('change', function() {
            syncPaymentMethod();
        });

        syncPaymentMethod();
    });
</script>
@endsection
