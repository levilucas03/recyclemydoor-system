<?php

namespace App\Services\WordPress;

use Automattic\WooCommerce\Client;
use App\Models\ListingPlatform;
use App\Models\Attribute;
use App\Models\AttributeGroup;

class WooCommerceService
{
    protected Client $client;

    public function __construct(ListingPlatform $platform)
    {
        $config = $platform->config;

        $this->client = new Client(
            $config['site_url'],
            $config['consumer_key'],
            $config['consumer_secret'],
            [
                'version' => 'wc/v3',
            ]
        );
    }

    public function republish(Product $product): void
    {
        $link = ListingPlatformLink::whereHas('listing.products', function ($query) use ($product) {
            $query->where('products.id', $product->id);
        })->first();

        if (! $link) {
            throw new \Exception('No listing found.');
        }

        $this->publish($link);
    }

    public function markProductSold(int $wordpressProductId): void
    {
        $this->client->put("products/{$wordpressProductId}", [
            'stock_status' => 'outofstock',
            'manage_stock' => true,
            'stock_quantity' => 0,
        ]);
    }


    public function test(): bool
    {
        try {

            $this->client->get('products', [
                'per_page' => 1,
            ]);

            return true;

        } catch (\Throwable $e) {

            report($e);

            return false;
        }
    }
}