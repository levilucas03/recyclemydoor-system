<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ListingPlatformLink;
use App\Services\WordPress\WooCommerceListingService;

class ListingPublishController extends Controller
{
    public function store(ListingPlatformLink $link)
    {
        if ($link->platform->slug !== 'wordpress') {
            return back()->with('error', 'Platform not supported yet.');
        }

        app(WooCommerceListingService::class)->publish($link);

        return back()->with('success', 'Listing pushed to WordPress.');
    }

    public function republish(
        ListingPlatformLink $link,
        WooCommerceListingService $woocommerce
    ) {
        $woocommerce->publish($link);

        return back()->with('success', 'Listing republished successfully.');
    }
}
