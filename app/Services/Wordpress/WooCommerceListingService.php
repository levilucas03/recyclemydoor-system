<?php

namespace App\Services\WordPress;

use App\Models\ListingPlatformLink;
use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\Storage;

class WooCommerceListingService
{
    public function publish(ListingPlatformLink $link): void
    {
        $link->load([
            'listing.products.primaryImage',
            'listing.products.categories',
            'platform',
        ]);

        $platform = $link->platform;
        $config = $platform->config;

        $client = new Client(
            $config['site_url'],
            $config['consumer_key'],
            $config['consumer_secret'],
            [
                'version' => 'wc/v3',
            ]
        );

        $product = $link->listing->products->first();

        if (! $product) {
            throw new \Exception('No product attached to this listing.');
        }

        $imageUrl = null;

        if ($product->primaryImage) {
            $imageUrl = url('/storage/' . $product->primaryImage->path);

            if (str_contains($imageUrl, 'localhost') || str_contains($imageUrl, '127.0.0.1')) {
                $imageUrl = null;
            }
        }

        if ($imageUrl) {
            $payload['images'] = [
                ['src' => $imageUrl],
            ];
        }

        $payload = [
            'name' => $product->title,
            'type' => 'simple',
            'status' => $config['default_status'] ?? 'draft',
            'regular_price' => (string) ($product->website_price ?? '0'),
            'description' => $product->description ?? '',
            'short_description' => $link->listing->notes ?? '',
            'sku' => $product->sku,
            'manage_stock' => true,
            'stock_quantity' => $product->quantity ?? 1,
        ];

        if ($imageUrl) {
            $payload['images'] = [
                ['src' => $imageUrl],
            ];
        }

        if ($product->categories?->count()) {
            $payload['categories'] = $product->categories->map(fn ($category) => [
                'name' => $category->name,
            ])->values()->all();
        }

        if ($link->external_id) {
            $response = $client->put("products/{$link->external_id}", $payload);
        } else {
            $response = $client->post('products', $payload);
        }

        $link->update([
            'external_id' => $response->id ?? $link->external_id,
            'status' => 'published',
            'payload' => $payload,
            'error' => null,
            'published_at' => now(),
        ]);
    }
}