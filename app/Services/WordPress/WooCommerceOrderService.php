<?php

namespace App\Services\WordPress;

use App\Models\ListingPlatform;
use App\Models\WooCommerceOrder;
use App\Models\WooCommerceOrderItem;
use App\Models\Product;
use Automattic\WooCommerce\Client;
use Carbon\Carbon;

class WooCommerceOrderService
{
    public function sync(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PLATFORM / CONNECTION
        |--------------------------------------------------------------------------
        */

        $platform = ListingPlatform::where('name', 'wordpress')
            ->firstOrFail();

        $config = $platform->config;

        $client = new Client(
            $config['site_url'],
            $config['consumer_key'],
            $config['consumer_secret'],
            [
                'version' => 'wc/v3',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | GET REAL ORDERS
        |--------------------------------------------------------------------------
        |
        | We're deliberately excluding checkout-draft, pending, failed etc.
        |
        */

        $orders = $client->get('orders', [
            'per_page' => 50,
            'orderby' => 'date',
            'order' => 'desc',
            'status' => 'processing,completed,on-hold',
        ]);


        /*
        |--------------------------------------------------------------------------
        | STORE ORDERS
        |--------------------------------------------------------------------------
        */

        foreach ($orders as $order) {

            $raw = json_decode(
                json_encode($order),
                true
            );

            /*
            |--------------------------------------------------------------------------
            | ORDER
            |--------------------------------------------------------------------------
            */

            $wooOrder = WooCommerceOrder::updateOrCreate(
                [
                    'woocommerce_order_id' => $order->id,
                ],
                [
                    'status' => $order->status ?? null,

                    'customer_email' =>
                        $order->billing->email ?? null,

                    'customer_phone' =>
                        $order->billing->phone ?? null,

                    'total' =>
                        (float) ($order->total ?? 0),

                    'shipping_total' =>
                        (float) ($order->shipping_total ?? 0),

                    'currency' =>
                        $order->currency ?? 'GBP',

                    'ordered_at' =>
                        isset($order->date_created)
                            ? Carbon::parse($order->date_created)
                            : null,

                    'raw' => $raw,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | LINE ITEMS
            |--------------------------------------------------------------------------
            */

            foreach ($order->line_items ?? [] as $lineItem) {

                /*
                |--------------------------------------------------------------------------
                | SKU
                |--------------------------------------------------------------------------
                |
                | Woo sometimes doesn't include SKU in the order line object,
                | depending on the response/setup.
                |
                */

                $sku = $lineItem->sku ?? null;


                /*
                |--------------------------------------------------------------------------
                | MATCH INTERNAL PRODUCT
                |--------------------------------------------------------------------------
                */

                $product = $sku
                    ? Product::where('sku', $sku)->first()
                    : null;


                /*
                |--------------------------------------------------------------------------
                | PRICE
                |--------------------------------------------------------------------------
                |
                | Woo line total is the actual amount charged for the line.
                |
                */

                $quantity = (int) ($lineItem->quantity ?? 1);

                $lineTotal = (float) ($lineItem->total ?? 0);

                $unitPrice = $quantity > 0
                    ? $lineTotal / $quantity
                    : $lineTotal;


                /*
                |--------------------------------------------------------------------------
                | STORE ITEM
                |--------------------------------------------------------------------------
                */

                WooCommerceOrderItem::updateOrCreate(
                    [
                        'woocommerce_order_id' =>
                            $wooOrder->id,

                        'woocommerce_line_item_id' =>
                            $lineItem->id,
                    ],
                    [
                        'product_id' =>
                            $product?->id,

                        'sku' =>
                            $sku,

                        'title' =>
                            $lineItem->name ?? null,

                        'quantity' =>
                            $quantity,

                        'price' =>
                            $unitPrice,

                        'total' =>
                            $lineTotal,

                        'raw' =>
                            json_decode(
                                json_encode($lineItem),
                                true
                            ),
                    ]
                );
            }
        }
    }
}