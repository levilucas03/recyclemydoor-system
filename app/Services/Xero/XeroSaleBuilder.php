<?php 

namespace App\Services\Xero;

use App\Models\Sale;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;

class XeroSaleBuilder
{
    public function buildLineItems(Sale $sale): array
    {

    
       return $sale->items->map(function ($item) {

            $lineItem = new LineItem();

            $lineItem
                ->setDescription($this->buildDescription($item))
                ->setQuantity($item->qty ?? 1)
                ->setUnitAmount($item->price ?? 0)
                ->setAccountCode($this->accountCodeFor($item))
                ->setTaxType($this->taxTypeFor($item));

            return $lineItem;

        })->toArray();
    }

    protected function buildDescription($item): string
    {
        // PRODUCT ITEMS
        if ($item->type === 'product' && $item->product) {

            return collect([
                $item->product->sku,
                $item->title,

                $item->product->width && $item->product->height 
                    ? "{$item->product->width}mm x {$item->product->height}mm"
                    : null,

            ])->filter()->implode(' | ');
        }

        // EVERYTHING ELSE
        return $item->title;
    }

    protected function accountCodeFor($item): string
    {
        return match ($item->type) {
            'delivery' => '208', // Delivery Income
            'product' => '200',
            'custom' => '310', // cost of goods
            default => '200',
        };
    }

    protected function taxTypeFor($item): string
    {
        if ((float) ($item->vat ?? 0) > 0) {
            return 'OUTPUT2';
        }

        return 'NONE';
    }
}