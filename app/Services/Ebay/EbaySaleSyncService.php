<?php

namespace App\Services\Ebay;

use App\Models\EbayOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Contact;
use App\Enums\ProductStatus;
use App\Enums\SaleStatus;
use Illuminate\Support\Facades\DB;

class EbaySaleSyncService
{
    public function sync(): void
    {
        EbayOrder::with('items.product')
            ->whereNull('sale_id')
            ->get()
            ->each(function (EbayOrder $ebayOrder) {
                DB::transaction(function () use ($ebayOrder) {

                    $contact = $this->findOrCreateContact($ebayOrder);

                    $sale = Sale::create([
                        'contact_id' => $contact?->id,

                        'ebay_id' => $ebayOrder->ebay_order_id,
                        'status' => $status = $this->mapSaleStatus($ebayOrder),
                        'invoice_date' => optional($ebayOrder->ordered_at)->toDateString(),
                        'notes' => 'Imported from eBay',
                        'customer_note' => 'eBay order: ' . $ebayOrder->ebay_order_id,
                        'total_amount' => $ebayOrder->total,
                        'total_vat_amount' => 0,

                        'fully_paid' => false,
                        'deposit_paid' => false,

                        'source_id' => 2, // ebay ID

                        'deliver_address_1' => data_get($ebayOrder->raw, 'fulfillmentStartInstructions.0.shippingStep.shipTo.contactAddress.addressLine1'),
                        'deliver_address_2' => data_get($ebayOrder->raw, 'fulfillmentStartInstructions.0.shippingStep.shipTo.contactAddress.addressLine2'),
                        'deliver_town_city' => data_get($ebayOrder->raw, 'fulfillmentStartInstructions.0.shippingStep.shipTo.contactAddress.city'),
                        'deliver_postcode' => data_get($ebayOrder->raw, 'fulfillmentStartInstructions.0.shippingStep.shipTo.contactAddress.postalCode'),
                    ]);

                    foreach ($ebayOrder->items as $item) {
                        $qty = $item->quantity ?? 1;
                        $price = $item->price ?? 0;
                        $total = $price * $qty;

                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'type' => $item->product_id ? 'product' : 'other',
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

                        if ($item->product && $status !== 'cancelled') {
                            $item->product->update([
                                'status' => ProductStatus::SOLD,
                            ]);
                        }
                    }

                    $deliveryCost = (float) data_get(
                        $ebayOrder->raw,
                        'pricingSummary.deliveryCost.value',
                        0
                    );

                    if ($deliveryCost > 0) {
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'type' => 'delivery',
                            'product_id' => null,
                            'title' => 'Delivery',
                            'description' => 'eBay delivery charge',
                            'price' => $deliveryCost,
                            'qty' => 1,
                            'discount' => 0,
                            'vat_amount' => 0,
                            'total' => $deliveryCost,
                            'account_code' => '208',
                            'note' => 'Imported from eBay',
                        ]);
                    }

                    $sale->update([
                        'reference' => $sale->generateReference(),
                    ]);

                    $ebayOrder->update([
                        'sale_id' => $sale->id,
                    ]);
                });
            });
    }

    protected function findOrCreateContact(EbayOrder $ebayOrder): ?Contact
    {
        $raw = $ebayOrder->raw ?? [];

        $buyerUsername = data_get($raw, 'buyer.username');
        $buyerEmail = data_get($raw, 'buyer.email');

        $shipTo = data_get($raw, 'fulfillmentStartInstructions.0.shippingStep.shipTo');

        $fullName = data_get($shipTo, 'fullName');
        $phone = data_get($shipTo, 'primaryPhone.phoneNumber');

        $address = data_get($shipTo, 'contactAddress');

        $nameParts = collect(explode(' ', trim($fullName ?? '')))
            ->filter()
            ->values();

        $firstName = $nameParts->first() ?: $buyerUsername ?: 'eBay';
        $lastName = $nameParts->count() > 1
            ? $nameParts->slice(1)->implode(' ')
            : 'Customer';

        $lookup = $buyerEmail
            ? ['email' => $buyerEmail]
            : ['ebay_username' => $buyerUsername];

        if (! $lookup[array_key_first($lookup)]) {
            return null;
        }

        return Contact::updateOrCreate(
            $lookup,
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'type' => 'customer',
                'email' => $buyerEmail,
                'telephone' => $phone,
                'mobile' => $phone,
                'address_1' => data_get($address, 'addressLine1'),
                'address_2' => data_get($address, 'addressLine2'),
                'town_city' => data_get($address, 'city'),
                'postcode' => data_get($address, 'postalCode'),
                'country' => data_get($address, 'countryCode'),
                'ebay_username' => $buyerUsername,
            ]
        );
    }

    protected function mapSaleStatus(EbayOrder $ebayOrder): string
    {
        $rawStatus = strtoupper($ebayOrder->status ?? '');

        return match ($rawStatus) {
            'CANCELLED' => 'cancelled',
            'FULFILLED' => 'awaiting_delivery',
            'IN_PROGRESS', 'NOT_STARTED' => 'draft',
            default => 'draft',
        };
    }
}