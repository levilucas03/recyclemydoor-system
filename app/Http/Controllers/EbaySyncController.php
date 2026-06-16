<?php

namespace App\Http\Controllers;

use App\Models\EbayAccount;
use App\Services\Ebay\EbayOrderService;
use App\Services\Ebay\EbaySaleSyncService;

class EbaySyncController extends Controller
{
    public function syncSales(
        EbayOrderService $orderService,
        EbaySaleSyncService $saleSyncService
    ) {
        $account = EbayAccount::where('is_active', true)->firstOrFail();

        $orderService->sync($account);
        $saleSyncService->sync();

        return back()->with('success', 'eBay sales synced successfully.');
    }
}