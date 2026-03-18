<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PurchaseController extends Controller
{
    private const PAYMENT_METHOD_MAP = [
        'convenience' => 1,
        'card' => 2,
    ];

    public function show(Item $item)
    {
        $user = auth()->user();

        return view('purchase.show', [
            'item' => $item->load(['mainImage', 'order']),
            'user' => $user,
            'shippingAddress' => $this->getShippingAddress(request(), $item, $user),
        ]);
    }

    public function editAddress(Item $item)
    {
        $user = auth()->user();

        return view('purchase.address', [
            'item' => $item->load(['mainImage']),
            'user' => $user,
            'shippingAddress' => $this->getShippingAddress(request(), $item, $user),
        ]);
    }

    public function updateAddress(Request $request, Item $item)
    {
        $validated = $request->validate(
            [
                'postal_code' => ['required', 'regex:/^\\d{3}-\\d{4}$/'],
                'address' => ['required', 'string', 'max:255'],
                'building' => ['nullable', 'string', 'max:255'],
            ],
            [
                'postal_code.required' => '郵便番号を入力してください',
                'postal_code.regex' => '郵便番号はハイフンありの8文字で入力してください',
                'address.required' => '住所を入力してください',
            ]
        );

        $request->session()->put($this->shippingSessionKey($item), $validated);

        return redirect()
            ->route('purchase.show', $item->id)
            ->with('status', '配送先住所を更新しました');
    }

    public function purchase(Request $request, Item $item)
    {
        if ($item->order()->exists()) {
            return redirect()
                ->route('items.index')
                ->with('status', 'この商品はすでに購入されています');
        }

        $validated = $request->validate(
            [
                'payment_method' => ['required', 'in:convenience,card'],
            ],
            [
                'payment_method.required' => '支払い方法を選択してください',
                'payment_method.in' => '支払い方法が不正です',
            ]
        );

        $user = $request->user();
        $shippingAddress = $this->getShippingAddress($request, $item, $user);

        if (empty($shippingAddress['postal_code']) || empty($shippingAddress['address'])) {
            return redirect()
                ->route('purchase.address.edit', $item->id)
                ->with('status', '購入前に配送先住所を登録してください');
        }

        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            return back()
                ->withErrors(['payment_method' => 'Stripeの秘密鍵が未設定です（STRIPE_SECRET を設定してください）'])
                ->withInput();
        }

        $stripePaymentType = $validated['payment_method'] === 'convenience' ? 'konbini' : 'card';

        $response = Http::withToken($secret)
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => route('purchase.complete', $item->id) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('purchase.show', $item->id),
                'payment_method_types' => [$stripePaymentType],
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => 'jpy',
                        'unit_amount' => $item->price,
                        'product_data' => [
                            'name' => $item->name,
                        ],
                    ],
                ]],
                'metadata' => [
                    'item_id' => (string) $item->id,
                    'user_id' => (string) $user->id,
                    'payment_method' => $validated['payment_method'],
                ],
            ]);

        if ($response->failed()) {
            return back()
                ->withErrors(['payment_method' => '決済画面の作成に失敗しました。時間をおいて再度お試しください'])
                ->withInput();
        }

        $payload = $response->json();
        $sessionId = $payload['id'] ?? null;
        $checkoutUrl = $payload['url'] ?? null;

        if (empty($sessionId) || empty($checkoutUrl)) {
            return back()
                ->withErrors(['payment_method' => '決済画面の作成に失敗しました。時間をおいて再度お試しください'])
                ->withInput();
        }

        $request->session()->put('stripe_checkout.' . $sessionId, [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'price' => $item->price,
            'payment_method' => $validated['payment_method'],
            'shipping_postal_code' => $shippingAddress['postal_code'],
            'shipping_address' => $shippingAddress['address'],
            'shipping_building' => $shippingAddress['building'],
        ]);

        return redirect()->away($checkoutUrl);
    }

    public function complete(Request $request, Item $item)
    {
        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId === '') {
            return redirect()
                ->route('items.index')
                ->with('status', '購入処理に失敗しました');
        }

        $checkoutData = $request->session()->get('stripe_checkout.' . $sessionId);
        if (
            empty($checkoutData)
            || (int) $checkoutData['item_id'] !== (int) $item->id
            || (int) $checkoutData['user_id'] !== (int) $request->user()->id
        ) {
            return redirect()
                ->route('items.index')
                ->with('status', '購入情報が確認できませんでした');
        }

        DB::transaction(function () use ($item, $checkoutData, $sessionId, $request) {
            $lockedItem = Item::whereKey($item->id)->lockForUpdate()->firstOrFail();
            $alreadyOrdered = Order::where('item_id', $lockedItem->id)->exists();

            if (!$alreadyOrdered) {
                Order::create([
                    'user_id' => $checkoutData['user_id'],
                    'item_id' => $lockedItem->id,
                    'price' => $checkoutData['price'],
                    'payment_method' => self::PAYMENT_METHOD_MAP[$checkoutData['payment_method']] ?? 1,
                    'shipping_postal_code' => $checkoutData['shipping_postal_code'],
                    'shipping_address' => $checkoutData['shipping_address'],
                    'shipping_building' => $checkoutData['shipping_building'],
                    'status' => 1,
                ]);
            }

            $request->session()->forget('stripe_checkout.' . $sessionId);
            $request->session()->forget($this->shippingSessionKey($item));
        });

        $isConvenience = ($checkoutData['payment_method'] ?? null) === 'convenience';

        return redirect()
            ->route('items.index')
            ->with('status', $isConvenience ? 'コンビニ支払い情報を発行しました' : '購入が完了しました');
    }

    private function getShippingAddress(Request $request, Item $item, $user): array
    {
        $shippingAddress = $request->session()->get($this->shippingSessionKey($item), []);

        return [
            'postal_code' => $shippingAddress['postal_code'] ?? $user->postal_code,
            'address' => $shippingAddress['address'] ?? $user->address,
            'building' => $shippingAddress['building'] ?? $user->building,
        ];
    }

    private function shippingSessionKey(Item $item): string
    {
        return 'purchase_shipping.' . $item->id;
    }
}
