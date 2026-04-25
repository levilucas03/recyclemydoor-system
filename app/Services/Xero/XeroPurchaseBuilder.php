<?php 

namespace App\Services\Xero;

use App\Models\Purchase;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;

class XeroPurchaseBuilder
{
    public function buildLineItems(Purchase $purchase): array
    {
        return $purchase->products->map(function ($product) {


            $lineItem = new LineItem();

            $lineItem
                ->setDescription($this->buildDescription($product))
                ->setQuantity(1)
                ->setUnitAmount($product->purchase_price ?? 0)
                ->setAccountCode('310')
                ->setTaxType('NONE');

            return $lineItem;

        })->toArray();
    }

    protected function buildDescription($product): string
    {
        return collect([
            $product->title,
            $product->sku,
        ])->filter()->join(' - ');
    }

    protected function getAccountCode($product): string
    {
        return $product->account_code ?? '310';
    }
}