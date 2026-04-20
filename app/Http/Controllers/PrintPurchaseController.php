<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\ProdigiProduct;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PrintPurchaseController extends Controller
{
    public function show(Photo $photo): View
    {
        $photographer = $photo->user;

        abort_if(!$this->isPhotoPurchasable($photo), 404);

        $enabledProducts = $photographer->enabledProdigiProducts()
            ->select('prodigi_products.*')
            ->orderBy('prodigi_products.category')
            ->orderBy('prodigi_products.name')
            ->get();

        return view('photos.buy-print', [
            'photo' => $photo,
            'photographer' => $photographer,
            'enabledProducts' => $enabledProducts,
        ]);
    }

    public function store(Request $request, Photo $photo): RedirectResponse
    {
        $photographer = $photo->user;

        abort_if(!$this->isPhotoPurchasable($photo), 404);

        $validated = $request->validate([
            'prodigi_product_id' => ['required', 'integer', 'exists:prodigi_products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'customer_email' => ['required', 'email:rfc,dns', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'shipping_address_line1' => ['required', 'string', 'max:255'],
            'shipping_address_line2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:255'],
            'shipping_county' => ['required', 'string', 'max:255'],
            'shipping_postcode' => ['required', 'string', 'max:32'],
            'shipping_country_code' => ['required', 'string', 'size:2'],
        ]);

        $prodigiProduct = ProdigiProduct::query()->findOrFail((int) $validated['prodigi_product_id']);
        $pivot = $photographer->prodigiProducts()
            ->where('prodigi_products.id', $prodigiProduct->id)
            ->first();

        if (!$pivot || !$pivot->pivot->is_enabled) {
            return back()->withErrors(['prodigi_product_id' => 'This print product is not available for this photographer.'])->withInput();
        }

        $unitPrice = (float) ($pivot->pivot->retail_price ?? 29.99);
        $quantity = (int) $validated['quantity'];
        $total = $unitPrice * $quantity;
        $platformFeeRate = 0.10;
        $platformFee = round($total * $platformFeeRate, 2);
        $photographerAmount = round($total - $platformFee, 2);

        $order = DB::transaction(function () use ($photographer, $photo, $prodigiProduct, $validated, $quantity, $unitPrice, $total, $platformFee, $photographerAmount) {
            $product = Product::query()->firstOrCreate(
                [
                    'user_id' => $photographer->id,
                    'photo_id' => $photo->id,
                    'prodigi_sku' => $prodigiProduct->sku,
                ],
                [
                    'name' => $prodigiProduct->name,
                    'description' => $prodigiProduct->description,
                    'type' => 'print_poster',
                    'price' => $unitPrice,
                    'active' => true,
                ]
            );

            if ((float) $product->price !== $unitPrice) {
                $product->update(['price' => $unitPrice]);
            }

            $order = Order::query()->create([
                'user_id' => $photographer->id,
                'customer_email' => $validated['customer_email'],
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'],
                'shipping_address_line1' => $validated['shipping_address_line1'],
                'shipping_address_line2' => $validated['shipping_address_line2'] ?? null,
                'shipping_city' => $validated['shipping_city'],
                'shipping_county' => $validated['shipping_county'],
                'shipping_postcode' => $validated['shipping_postcode'],
                'shipping_country_code' => strtoupper($validated['shipping_country_code']),
                'total' => $total,
                'platform_fee' => $platformFee,
                'photographer_amount' => $photographerAmount,
                'status' => 'pending',
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $unitPrice,
            ]);

            return $order;
        });

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => route('print-purchase.success', ['order' => $order->id]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('print-purchase.cancel', ['order' => $order->id]),
            'payment_method_types' => ['card'],
            'customer_email' => $validated['customer_email'],
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'photo_id' => (string) $photo->id,
                    'photographer_id' => (string) $photographer->id,
                ],
            ],
            'line_items' => [
                [
                    'quantity' => $quantity,
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => (int) round($unitPrice * 100),
                        'product_data' => [
                            'name' => $prodigiProduct->name,
                            'description' => 'Print of photo by @' . $photographer->username,
                            'metadata' => [
                                'prodigi_sku' => $prodigiProduct->sku,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return redirect()->away($session->url);
    }

    public function success(Order $order): View
    {
        return view('photos.purchase-success', ['order' => $order]);
    }

    public function cancel(Order $order): View
    {
        return view('photos.purchase-cancel', ['order' => $order]);
    }

    private function isPhotoPurchasable(Photo $photo): bool
    {
        $photographer = $photo->user;

        return $photographer
            && $photographer->hasFeature('selling')
            && $photographer->enabledProdigiProducts()->exists()
            && !$photo->hide_from_portfolio
            && $photo->set
            && $photo->set->collection
            && !$photo->set->hide_from_portfolio
            && !$photo->set->collection->hide_from_portfolio
            && !$photo->set->collection->private;
    }
}
