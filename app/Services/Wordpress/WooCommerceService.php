<?php

namespace App\Services\WordPress;

use Automattic\WooCommerce\Client;
use App\Models\ListingPlatform;

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