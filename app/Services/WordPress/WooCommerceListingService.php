<?php

namespace App\Services\WordPress;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\ListingPlatform;
use App\Models\Category;

use App\Models\ListingPlatformLink;
use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\Storage;

class WooCommerceListingService
{
    public function publish(ListingPlatformLink $link): void
    {

        $link->load([
            'listing.products.primaryImage',
            'listing.products.images',
            'listing.products.categories',
            'platform',
        ]);

        $platform = $link->platform;
        $config = $platform->config;
        $images = [];

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

        if ($link->sync_images) {

            $images = $product->images
                ->sortBy('sort_order')
                ->values()
                ->map(function ($image, $index) {
                    if ($image->wordpress_image_id) {
                        return [
                            'id' => (int) $image->wordpress_image_id,
                            'position' => $index,
                        ];
                    }

                    $url = url('/storage/' . $image->path);

                    if (str_contains($url, 'localhost') || str_contains($url, '127.0.0.1')) {
                        return null;
                    }

                    return [
                        'src' => $url,
                        'position' => $index,
                    ];
                })
                ->filter()
                ->values()
                ->all();        
    
        }           

       

        $websitePrice = $product->prices()
            ->where('type', 'website')
            ->value('price');


   
        $categories = $product->categories
            ->filter(fn ($category) => $category->wordpress_term_id)
            ->map(fn ($category) => [
                'id' => (int) $category->wordpress_term_id,
            ])
            ->values()
            ->toArray();

        $attributes = $product->attributes
            ->filter(fn ($attribute) => $attribute->wordpress_attribute_id && $attribute->wordpress_slug)
            ->groupBy('wordpress_attribute_id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'id' => (int) $first->wordpress_attribute_id,
                    'options' => $items
                        ->pluck('wordpress_slug')
                        ->unique()
                        ->values()
                        ->toArray(),
                    'visible' => true,
                    'variation' => false,
                ];
            })
            ->values()
            ->toArray();

        $dimensions = [
            'length' => (string) ($product->depth / 10 ?? ''),
            'width'  => (string) ($product->width / 10 ?? ''),
            'height' => (string) ($product->height / 10 ?? ''),
        ];

        $payload = [
            'name' => $link->listing->title ?? $product->title,
            'type' => 'simple',
            'status' => $config['default_status'] ?? 'draft',
            'regular_price' => (string) ($websitePrice ?? 0),
            'description' => $product->description ?? '',
            'short_description' => $link->listing->notes ?? '',
            'sku' => $product->sku,
            'manage_stock' => true,
            'stock_quantity' => $product->quantity ?? 1,
            'categories' => $categories,
            'attributes' => $attributes,
            'dimensions' => $dimensions,
        ];
        

       if (!empty($images)) {
            $payload['images'] = $images;
        }

        if ($link->external_id) {
            $client->put("products/{$link->external_id}", [
                'categories' => [],
                'attributes' => [],
            ]);

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


        $product->update([
            'status' => 'listed'
        ]);

        if (!empty($response->images)) {
            $localImages = $product->images()
                ->orderBy('sort_order')
                ->get()
                ->values();

            foreach ($response->images as $index => $wpImage) {

                $localImage = $localImages->get($index);

                if ($localImage && isset($wpImage->id)) {
                    $localImage->update([
                        'wordpress_image_id' => $wpImage->id,
                    ]);
                }
            }
        }
    }

    public function syncAttributeGroupToWordPress(
        ListingPlatform $platform,
        AttributeGroup $attributeGroup
    ): void {
        $config = $platform->config;

        $client = new Client(
            $config['site_url'],
            $config['consumer_key'],
            $config['consumer_secret'],
            [
                'version' => 'wc/v3',
            ]
        );

        $attributeGroup->load('attributes');

        $taxonomy = 'pa_' . str($attributeGroup->name)->slug('_');

        // 1. Find or create the WooCommerce ATTRIBUTE, e.g. Material
        $existingAttributes = collect($client->get('products/attributes', [
            'search' => $attributeGroup->name,
            'per_page' => 100,
        ]));

        $wooAttribute = $existingAttributes->first(function ($item) use ($attributeGroup) {
            return strtolower($item->name) === strtolower($attributeGroup->name);
        });

        if (! $wooAttribute) {
            $wooAttribute = $client->post('products/attributes', [
                'name' => $attributeGroup->name,
                'slug' => str($attributeGroup->name)->slug('_'),
                'type' => 'select',
                'order_by' => 'menu_order',
                'has_archives' => true,
            ]);
        }

        // Optional but recommended if you added fields to attribute_groups
        $attributeGroup->update([
            'wordpress_attribute_id' => $wooAttribute->id,
            'wordpress_slug' => $wooAttribute->slug,
        ]);

        // 2. Find or create the TERMS under that attribute
        foreach ($attributeGroup->attributes as $attribute) {
            $existingTerms = collect($client->get("products/attributes/{$wooAttribute->id}/terms", [
                'search' => $attribute->name,
                'per_page' => 100,
            ]));

            $term = $existingTerms->first(function ($item) use ($attribute) {
                return strtolower($item->name) === strtolower($attribute->name);
            });

            if (! $term) {
                $term = $client->post("products/attributes/{$wooAttribute->id}/terms", [
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                ]);
            }

            $attribute->update([
                'wordpress_term_id' => $term->id,
                'wordpress_slug' => $term->slug,
                'wordpress_taxonomy' => $taxonomy,
                'wordpress_attribute_id' => $wooAttribute->id,
            ]);
        }
    }

    public function syncCategoryToWordPress(
        ListingPlatform $platform,
        Category $category
    ): void {
        $config = $platform->config;

        $client = new Client(
            $config['site_url'],
            $config['consumer_key'],
            $config['consumer_secret'],
            ['version' => 'wc/v3']
        );

        $existing = collect($client->get('products/categories', [
            'slug' => $category->slug,
            'per_page' => 100,
        ]));

        $term = $existing->first();

        if (! $term) {
            $existing = collect($client->get('products/categories', [
                'search' => $category->name,
                'per_page' => 100,
            ]));

            $term = $existing->first(function ($item) use ($category) {
                return strtolower($item->name) === strtolower($category->name);
            });
        }

        if (! $term) {
            try {
                $term = $client->post('products/categories', [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ]);
            } catch (\Exception $e) {
                $existing = collect($client->get('products/categories', [
                    'search' => $category->name,
                    'per_page' => 100,
                ]));

                $term = $existing->first(function ($item) use ($category) {
                    return strtolower($item->name) === strtolower($category->name);
                });

                if (! $term) {
                    throw $e;
                }
            }
        }

        $category->update([
            'wordpress_term_id' => $term->id,
            'wordpress_slug' => $term->slug,
        ]);
    }
}