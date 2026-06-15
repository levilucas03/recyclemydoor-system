<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\WordPress\WooCommerceService;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {

        if (! $product->wasChanged('status')) {
            return;
        }

        $status = $product->status instanceof \BackedEnum
            ? $product->status->value
            : $product->status;

        if ($status !== 'sold') {
            return;
        }

        $product->load('listing.platformLinks.platform');

        $listing = $product->listing;

        if (! $listing) {
            return;
        }

        foreach ($listing->platformLinks as $link) {
            if ($link->platform?->slug !== 'wordpress') {
                continue;
            }

            if (! $link->external_id) {
                continue;
            }

            $service = new WooCommerceService($link->platform);

            $service->markProductSold((int) $link->external_id);

            
        }

        
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
