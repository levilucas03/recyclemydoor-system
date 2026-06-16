<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Ebay\EbaySaleSyncService;

class EbayCreateSales extends Command
{
    protected $signature = 'ebay:create-sales';

    protected $description = 'Create internal sales from synced eBay orders';

    public function handle(EbaySaleSyncService $service): int
    {
        $service->sync();

        $this->info('eBay sales created successfully.');

        return self::SUCCESS;
    }
}