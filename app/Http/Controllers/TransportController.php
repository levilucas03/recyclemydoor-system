<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use Inertia\Inertia;

class TransportController extends Controller
{
    public function index()
    {
        /*
         * SALES WAITING FOR DELIVERY
         */
        $sales = Sale::query()
            ->with([
                'contact',
                'items.product',
            ])
            ->where('status', 'awaiting_delivery')


            // Change this to whatever identifies something
            // still requiring delivery in your system.
            // ->where('delivery_method', 'delivery')

            ->orderBy('invoice_date')
            ->get()
            ->map(function ($sale) {

                return [
                    'id' => 'sale-' . $sale->id,

                    'record_id' => $sale->id,

                    'type' => 'delivery',

                    'reference' => $sale->reference,

                    'name' => $sale->contact?->name,

                    'postcode' => $sale->deliver_postcode,

                    'address' => collect([
                        $sale->deliver_address_1 ?? null,
                        $sale->deliver_address_2 ?? null,
                        $sale->deliver_town_city ?? null,
                        $sale->deliver_postcode ?? null,
                    ])->filter()->implode(', '),

                    'date' => $sale->invoice_date,

                    'items' => $sale->items
                        ->where('type', 'product')
                        ->map(function ($item) {

                            return [
                                'name' => $item->product?->title
                                    ?? $item->description
                                    ?? 'Product',

                                'qty' => $item->qty,
                            ];
                        })
                        ->values(),
                ];
            });


        /*
         * PURCHASES WAITING FOR COLLECTION
         */
        $purchases = Purchase::query()
            ->with([
                'contact',
                'products',
            ])

            // We can refine this depending on your Purchase model.
            ->where('status', 'awaiting_collection')
            ->orWhere('status', 'on_hold')

            ->orderBy('purchase_date')
            ->get()
            ->map(function ($purchase) {

                return [
                    'id' => 'purchase-' . $purchase->id,

                    'record_id' => $purchase->id,

                    'type' => 'collection',

                    'reference' => $purchase->reference ?? 'P-' . $purchase->id,

                    'name' => $purchase->contact?->name,

                    /*
                     * Change these fields if collection address
                     * is stored somewhere else.
                     */
                    'postcode' => $purchase->postcode
                        ?? $purchase->contact?->postcode,

                    'address' => collect([
                        $purchase->address_line_1
                            ?? $purchase->contact?->address_line_1,

                        $purchase->address_line_2
                            ?? $purchase->contact?->address_line_2,

                        $purchase->city
                            ?? $purchase->contact?->city,

                        $purchase->postcode
                            ?? $purchase->contact?->postcode,
                    ])->filter()->implode(', '),

                    'date' => $purchase->purchase_date,

                    'items' => $purchase->products
                        ->map(function ($product) {

                            return [
                                'name' => $product->title ?? 'Product',
                                'qty' => 1,
                            ];
                        })
                        ->values(),
                ];
            });


        /*
         * COMBINE BOTH
         */
        $jobs = $purchases
            ->concat($sales)
            // ->filter(fn ($job) => !empty($job['postcode'])) take out filter for now
            ->values();


        return Inertia::render('transport/Index', [
            'jobs' => $jobs,
        ]);
    }
}