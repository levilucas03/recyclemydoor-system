<?php

namespace App\Http\Controllers;

use App\Services\WordPress\WooCommerceOrderService;

class WooCommerceSyncController extends Controller
{
    public function test(
        WooCommerceOrderService $orderService
    ) {
        $orderService->sync();

        return back()->with(
            'success',
            'WooCommerce orders imported.'
        );
    }
}