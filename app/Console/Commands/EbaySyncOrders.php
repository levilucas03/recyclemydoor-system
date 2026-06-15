<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EbayAccount;
use App\Services\Ebay\EbayOrderService;

class EbaySyncOrders extends Command
{
    protected $signature = 'ebay:sync-orders';

    protected $description = 'Sync orders from eBay';

    public function handle(EbayOrderService $service): int
    {
        $account = EbayAccount::where('is_active', true)->first();

        if (! $account) {
            $this->error('No active eBay account found.');

            return self::FAILURE;
        }

        $service->sync($account);

        $this->info('Orders synced successfully.');

        return self::SUCCESS;
    }
}