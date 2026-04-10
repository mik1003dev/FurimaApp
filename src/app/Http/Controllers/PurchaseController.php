<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
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

    public function show(Item $item_id)
    {
        $item = $item_id;
        $user = auth()->user();

        // 出品者本人は自分の商品を購入できないようにする。
        if ((int) $item->user_id === (int) $user->id) {
            return redirect()
                ->route('items.show', $item)
                ->with('status', '自分が出品した商品は購入できません');
        }

        // 購入確認画面に商品情報と配送先情報を渡す。
        return view('purchase.show', [
            'item' => $item->load(['mainImage', 'order']),
            'user' => $user,
            'shippingAddress' => $this->getShippingAddress(request(), $item, $user),
        ]);
    }

    public function editAddress(Item $item_id)
    {
        $item = $item_id;
        $user = auth()->user();

        // 出品者本人は自分の商品を購入できないようにする。
        if ((int) $item->user_id === (int) $user->id) {
            return redirect()
                ->route('items.show', $item)
                ->with('status', '自分が出品した商品は購入できません');
        }

        // 配送先編集画面には商品情報と現在の配送先候補を表示する。
        return view('purchase.address', [
            'item' => $item->load(['mainImage']),
            'user' => $user,
            'shippingAddress' => $this->getShippingAddress(request(), $item, $user),
        ]);
    }

    public function updateAddress(AddressRequest $request, Item $item_id)
    {
        $item = $item_id;

        // 出品者本人は自分の商品を購入できないようにする。
        if ((int) $item->user_id === (int) $request->user()->id) {
            return redirect()
                ->route('items.show', $item)
                ->with('status', '自分が出品した商品は購入できません');
        }

        $validated = $request->validated();

        // 入力された配送先を商品単位でセッションに保持する。
        $request->session()->put($this->shippingSessionKey($item), $validated);

        return redirect()
            ->route('purchase.show', $item->id)
            ->with('status', '配送先住所を更新しました');
    }

    public function purchase(PurchaseRequest $request, Item $item_id)
    {
        $item = $item_id;

        // 出品者本人は自分の商品を購入できないようにする。
        if ((int) $item->user_id === (int) $request->user()->id) {
            return redirect()
                ->route('items.show', $item)
                ->with('status', '自分が出品した商品は購入できません');
        }

        // すでに購入済みの商品は重複購入させない。
        if ($item->order()->exists()) {
            return redirect()
                ->route('items.index')
                ->with('status', 'この商品はすでに購入されています');
        }

        $validated = $request->validated();

        $user = $request->user();
        $shippingAddress = $this->getShippingAddress($request, $item, $user);

        // 配送先が未登録の場合は住所入力画面へ戻す。
        if (empty($shippingAddress['postal_code']) || empty($shippingAddress['address'])) {
            return redirect()
                ->route('purchase.address.edit', $item->id)
                ->with('status', '購入前に配送先住所を登録してください');
        }

        $secret = config('services.stripe.secret');
        // Stripe の設定不足時は決済処理を開始しない。
        if (empty($secret)) {
            return back()
                ->withErrors(['payment_method' => 'Stripeの秘密鍵が未設定です（STRIPE_SECRET を設定してください）'])
                ->withInput();
        }

        $stripePaymentType = $validated['payment_method'] === 'convenience' ? 'konbini' : 'card';

        // Stripe Checkout セッションを作成し、外部決済画面へ遷移させる。
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

        // セッションIDと遷移先URLが取得できなければエラー扱いにする。
        if (empty($sessionId) || empty($checkoutUrl)) {
            return back()
                ->withErrors(['payment_method' => '決済画面の作成に失敗しました。時間をおいて再度お試しください'])
                ->withInput();
        }

        // 決済完了後に注文登録できるよう必要情報をセッションへ保持する。
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

    public function complete(Request $request, Item $item_id)
    {
        $item = $item_id;

        $sessionId = (string) $request->query('session_id', '');
        // Stripe からの戻り値にセッションIDが無い場合は購入失敗とする。
        if ($sessionId === '') {
            return redirect()
                ->route('items.index')
                ->with('status', '購入処理に失敗しました');
        }

        $checkoutData = $request->session()->get('stripe_checkout.' . $sessionId);
        // セッション内の購入情報が一致しない場合は不正な完了処理を防ぐ。
        if (
            empty($checkoutData)
            || (int) $checkoutData['item_id'] !== (int) $item->id
            || (int) $checkoutData['user_id'] !== (int) $request->user()->id
        ) {
            return redirect()
                ->route('items.index')
                ->with('status', '購入情報が確認できませんでした');
        }

        // 排他制御をかけながら注文を作成し、関連セッションを破棄する。
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

        // セッション上の配送先があれば優先し、なければユーザー登録住所を使う。
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
