<?php

namespace App\Http\Controllers;

use App\Services\WordPress\WooCommerceOrderService;
use App\Services\WordPress\WooCommerceSaleSyncService;

class WooCommerceSyncController extends Controller
{
    public function syncSales(
        WooCommerceOrderService $orderService,
        WooCommerceSaleSyncService $saleSyncService
    ) {
        $orderService->sync();

        $saleSyncService->sync();

        return back()->with(
            'success',
            'Website sales synced successfully.'
        );
    }
}