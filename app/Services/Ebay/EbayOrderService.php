<?php

namespace App\Services\Ebay;

use App\Models\EbayAccount;
use App\Models\EbayOrder;
use App\Models\EbayOrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

class EbayOrderService
{
    public function sync(EbayAccount $account): void
    {
        $response = Http::withToken($account->access_token)
            ->get('https://api.ebay.com/sell/fulfillment/v1/order', [
                'limit' => 50,
            ]);

        if ($response->failed()) {
            dd($response->status(), $response->json(), $response->body());
        }

        foreach ($response->json('orders', []) as $order) {
            $ebayOrder = EbayOrder::updateOrCreate(
                ['ebay_order_id' => $order['orderId']],
                [
                    'ebay_account_id' => $account->id,
                    'status' => $order['orderFulfillmentStatus'] ?? null,
                    'buyer_username' => $order['buyer']['username'] ?? null,
                    'buyer_email' => $order['buyer']['email'] ?? null,
                    'total' => $order['pricingSummary']['total']['value'] ?? 0,
                    'currency' => $order['pricingSummary']['total']['currency'] ?? 'GBP',
                    'ordered_at' => isset($order['creationDate'])
                        ? \Carbon\Carbon::parse($order['creationDate'])
                        : null,
                    'raw' => $order,
                ]
            );

            foreach ($order['lineItems'] ?? [] as $lineItem) {
                $sku = $lineItem['sku'] ?? null;

                $product = $sku
                    ? Product::where('sku', $sku)->first()
                    : null;

                EbayOrderItem::updateOrCreate(
                    [
                        'ebay_order_id' => $ebayOrder->id,
                        'ebay_line_item_id' => $lineItem['lineItemId'] ?? null,
                    ],
                    [
                        'product_id' => $product?->id,
                        'sku' => $sku,
                        'title' => $lineItem['title'] ?? null,
                        'quantity' => $lineItem['quantity'] ?? 1,
                        'price' => $lineItem['lineItemCost']['value'] ?? 0,
                        'raw' => $lineItem,
                    ]
                );
            }
        }
    }
}