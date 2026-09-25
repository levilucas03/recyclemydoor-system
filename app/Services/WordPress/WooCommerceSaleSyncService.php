<?php

namespace App\Services\WordPress;

use App\Models\WooCommerceOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Contact;
use App\Models\ProductPrice;
use App\Enums\ProductStatus;
use Illuminate\Support\Facades\DB;

class WooCommerceSaleSyncService
{
    public function sync(): void
    {
        WooCommerceOrder::with('items.product')
            ->whereNull('sale_id')

            // Only genuine orders
            ->whereIn('status', [
                'processing',
                'completed',
                'on-hold',
            ])

            ->get()

            ->each(function (WooCommerceOrder $wooOrder) {

                DB::transaction(function () use ($wooOrder) {

                    /*
                    |--------------------------------------------------------------------------
                    | CONTACT
                    |--------------------------------------------------------------------------
                    */

                    $contact = $this->findOrCreateContact($wooOrder);


                    /*
                    |--------------------------------------------------------------------------
                    | SALE STATUS
                    |--------------------------------------------------------------------------
                    */

                    $status = $this->mapSaleStatus($wooOrder);


                    /*
                    |--------------------------------------------------------------------------
                    | CREATE SALE
                    |--------------------------------------------------------------------------
                    */

                    $sale = Sale::create([

                        'contact_id' => $contact?->id,
                        'user_id' => '1',

                        'status' => $status,

                        'invoice_date' => optional(
                            $wooOrder->ordered_at
                        )->toDateString(),

                        'notes' => 'Imported from website',

                        'customer_note' =>
                            'Website order: #' .
                            $wooOrder->woocommerce_order_id,

                        'total_amount' => $wooOrder->total,

                        'total_vat_amount' => 0,

                        /*
                        |--------------------------------------------------------------------------
                        | PAYMENT
                        |--------------------------------------------------------------------------
                        |
                        | WooCommerce processing/completed normally means the
                        | checkout has been paid.
                        |
                        */

                        'fully_paid' => in_array(
                            $wooOrder->status,
                            ['processing', 'completed']
                        ),

                        'deposit_paid' => false,

                        /*
                        |--------------------------------------------------------------------------
                        | SOURCE
                        |--------------------------------------------------------------------------
                        |
                        | IMPORTANT:
                        | Change this to your actual Website source ID.
                        | eBay is currently source_id = 2 in your existing importer.
                        |
                        */

                        'source_id' => 1,

                        /*
                        |--------------------------------------------------------------------------
                        | DELIVERY ADDRESS
                        |--------------------------------------------------------------------------
                        */

                        'deliver_address_1' => $this->addressValue(
                            $wooOrder,
                            'address_1'
                        ),

                        'deliver_address_2' => $this->addressValue(
                            $wooOrder,
                            'address_2'
                        ),

                        'deliver_town_city' => $this->addressValue(
                            $wooOrder,
                            'city'
                        ),

                        'deliver_postcode' => $this->addressValue(
                            $wooOrder,
                            'postcode'
                        ),
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | SALE ITEMS
                    |--------------------------------------------------------------------------
                    */

                    foreach ($wooOrder->items as $item) {

                        $qty = (int) ($item->quantity ?? 1);

                        $price = (float) ($item->price ?? 0);

                        $total = (float) ($item->total ?? 0);


                        SaleItem::create([

                            'sale_id' => $sale->id,

                            'type' => $item->product_id
                                ? 'product'
                                : 'other',

                            'product_id' => $item->product_id,

                            'title' => $item->title,

                            'description' => $item->sku
                                ? 'SKU: ' . $item->sku
                                : null,

                            'price' => $price,

                            'qty' => $qty,

                            'discount' => 0,

                            'vat_amount' => 0,

                            'total' => $total,

                            'account_code' => '200',
                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | PRODUCT SOLD
                        |--------------------------------------------------------------------------
                        */

                        if ($item->product) {

                            $item->product->update([
                                'status' => ProductStatus::SOLD,
                            ]);


                            /*
                            |--------------------------------------------------------------------------
                            | SOLD PRICE
                            |--------------------------------------------------------------------------
                            |
                            | Save what the customer actually paid for the
                            | product, not its original website/list price.
                            |
                            */

                            ProductPrice::updateOrCreate(
                                [
                                    'product_id' => $item->product->id,
                                    'type' => 'sold',
                                ],
                                [
                                    'price' => $price,
                                ]
                            );
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | DELIVERY
                    |--------------------------------------------------------------------------
                    */

                    $deliveryCost = (float) (
                        $wooOrder->shipping_total ?? 0
                    );

                    if ($deliveryCost > 0) {

                        SaleItem::create([

                            'sale_id' => $sale->id,

                            'type' => 'delivery',

                            'product_id' => null,

                            'title' => 'Delivery',

                            'description' =>
                                'Website delivery charge',

                            'price' => $deliveryCost,

                            'qty' => 1,

                            'discount' => 0,

                            'vat_amount' => 0,

                            'total' => $deliveryCost,

                            'account_code' => '208',

                            'note' =>
                                'Imported from website',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | INTERNAL REFERENCE
                    |--------------------------------------------------------------------------
                    */

                    $sale->update([
                        'reference' =>
                            $sale->generateReference(),
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | MARK WOO ORDER IMPORTED
                    |--------------------------------------------------------------------------
                    |
                    | This prevents the same website order from creating
                    | another Sale next time sync() runs.
                    |
                    */

                    $wooOrder->update([
                        'sale_id' => $sale->id,
                    ]);
                });
            });
    }


    /*
    |--------------------------------------------------------------------------
    | CONTACT
    |--------------------------------------------------------------------------
    */

    protected function findOrCreateContact(
        WooCommerceOrder $wooOrder
    ): ?Contact {

        $raw = $wooOrder->raw ?? [];

        $billing = data_get($raw, 'billing', []);
        $shipping = data_get($raw, 'shipping', []);

        $email = data_get($billing, 'email');

        $phone = data_get($billing, 'phone');


        /*
        |--------------------------------------------------------------------------
        | CUSTOMER NAME
        |--------------------------------------------------------------------------
        */

        $firstName =
            data_get($billing, 'first_name')
            ?: data_get($shipping, 'first_name')
            ?: 'Website';

        $lastName =
            data_get($billing, 'last_name')
            ?: data_get($shipping, 'last_name')
            ?: 'Customer';


        /*
        |--------------------------------------------------------------------------
        | CONTACT LOOKUP
        |--------------------------------------------------------------------------
        |
        | Email is the safest identifier from WooCommerce.
        |
        */

        if (!$email) {
            return null;
        }


        return Contact::updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'first_name' => $firstName,

                'last_name' => $lastName,

                'type' => 'customer',

                'email' => $email,

                'telephone' => $phone,

                'mobile' => $phone,

                /*
                |--------------------------------------------------------------------------
                | CONTACT ADDRESS
                |--------------------------------------------------------------------------
                |
                | Use billing address for the contact record.
                | The Sale itself stores the actual delivery address.
                |
                */

                'address_1' =>
                    data_get($billing, 'address_1'),

                'address_2' =>
                    data_get($billing, 'address_2'),

                'town_city' =>
                    data_get($billing, 'city'),

                'postcode' =>
                    data_get($billing, 'postcode'),

                'country' =>
                    data_get($billing, 'country'),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DELIVERY ADDRESS
    |--------------------------------------------------------------------------
    |
    | WooCommerce shipping fields can sometimes be blank when billing and
    | shipping are the same. Fall back to billing in that situation.
    |
    */

    protected function addressValue(
        WooCommerceOrder $wooOrder,
        string $field
    ): ?string {

        $raw = $wooOrder->raw ?? [];

        return data_get($raw, "shipping.{$field}")
            ?: data_get($raw, "billing.{$field}");
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    protected function mapSaleStatus(
        WooCommerceOrder $wooOrder
    ): string {

        return match ($wooOrder->status) {

            'completed' =>
                'complete',

            'processing',
            'on-hold' =>
                'awaiting_delivery',

            'cancelled',
            'refunded',
            'failed' =>
                'cancelled',

            default =>
                'draft',
        };
    }
}