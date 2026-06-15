<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ListingPlatform;
use App\Services\WordPress\WooCommerceService;
use App\Services\WordPress\WooCommerceListingService;
use App\Models\AttributeGroup;
use App\Models\Attribute;
use App\Models\Category;

use App\Models\ListingPlatformLink;
use Automattic\WooCommerce\Client;




class ListingPlatformController extends Controller
{
    public function index()
    {
        return Inertia::render('listing-platforms/Index', [
            'platforms' => ListingPlatform::orderBy('name')->get(),
        ]);
    }

    public function edit(ListingPlatform $listingPlatform)
    {
        return inertia('listing-platforms/Edit', [
            'platform' => $listingPlatform,
            'attributeGroups' => AttributeGroup::with('attributes')
                ->orderBy('name')
                ->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ListingPlatform $listingPlatform)
    {
        $validated = $request->validate([
            'is_active' => ['boolean'],
            'config.site_url' => ['nullable', 'url'],
            'config.consumer_key' => ['nullable', 'string'],
            'config.consumer_secret' => ['nullable', 'string'],
            'config.default_status' => ['required', 'in:draft,publish'],
        ]);

        $listingPlatform->update($validated);

        return back()->with('success', 'Platform updated');
    }

    public function test(ListingPlatform $listingPlatform)
    {
        try {

            $success = match ($listingPlatform->slug) {

                'wordpress' => app(WooCommerceService::class, [
                    'platform' => $listingPlatform
                ])->test(),

                default => false,
            };

            return back()->with(
                $success ? 'success' : 'error',
                $success
                    ? 'Connection successful'
                    : 'Connection failed'
            );

        } catch (\Throwable $e) {

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    public function updateWordPressAttributes(Request $request, ListingPlatform $listingPlatform)
    {
        $data = $request->validate([
            'attributes' => ['required', 'array'],
            'attributes.*.id' => ['required', 'exists:attributes,id'],
            'attributes.*.wordpress_term_id' => ['nullable', 'integer'],
            'attributes.*.wordpress_slug' => ['nullable', 'string', 'max:255'],
            'attributes.*.wordpress_taxonomy' => ['nullable', 'string', 'max:255'],
            'attributes.*.wordpress_attribute_id' => ['nullable', 'integer'],
        ]);

        foreach ($data['attributes'] as $attributeData) {
            Attribute::where('id', $attributeData['id'])->update([
                'wordpress_term_id' => $attributeData['wordpress_term_id'] ?? null,
                'wordpress_slug' => $attributeData['wordpress_slug'] ?? null,
                'wordpress_taxonomy' => $attributeData['wordpress_taxonomy'] ?? null,
                'wordpress_attribute_id' => $attributeData['wordpress_attribute_id'] ?? null,
            ]);
        }

        return back()->with('success', 'WordPress attribute mappings updated.');
    }

    public function updateWordPressCategories(Request $request, ListingPlatform $platform)
    {
        $data = $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'exists:categories,id'],
            'categories.*.wordpress_term_id' => ['nullable'],
            'categories.*.wordpress_slug' => ['nullable', 'string'],
        ]);

        foreach ($data['categories'] as $categoryData) {
            Category::where('id', $categoryData['id'])->update([
                'wordpress_term_id' => $categoryData['wordpress_term_id'] ?: null,
                'wordpress_slug' => $categoryData['wordpress_slug'] ?: null,
            ]);
        }

        return back()->with('success', 'Category mappings saved.');
    }

    public function syncWordPressAttributeGroup(
        ListingPlatform $listingPlatform,
        AttributeGroup $attributeGroup,
        WooCommerceListingService $woocommerce
    ) {
        $woocommerce->syncAttributeGroupToWordPress($listingPlatform, $attributeGroup);

        return back()->with('success', 'Attribute group synced to WordPress.');
    }

    public function syncCategories(ListingPlatform $listingPlatform)
    {
        Category::query()
            ->orderBy('name')
            ->get()
            ->each(function ($category) use ($listingPlatform) {
                app(WooCommerceListingService::class)
                    ->syncCategoryToWordPress($listingPlatform, $category);
            });

        return back()->with('success', 'Categories synced to WordPress.');
    }

    public function findWordPressProduct(ListingPlatformLink $link)
    {
        $link->load(['listing.products', 'platform']);

        $product = $link->listing->products->first();

        if (! $product) {
            return back()->with('error', 'No product attached to this listing.');
        }

        $config = $link->platform->config;

        $client = new Client(
            rtrim($config['site_url'], '/'),
            $config['consumer_key'],
            $config['consumer_secret'],
            ['version' => 'wc/v3']
        );

        $matches = collect();

        if ($product->sku) {
            $matches = collect($client->get('products', [
                'sku' => $product->sku,
                'per_page' => 10,
            ]));
        }

        if ($matches->isEmpty()) {
            $matches = collect($client->get('products', [
                'search' => $product->title,
                'per_page' => 10,
            ]));
        }

        $wooProduct = $matches->first();

        if (! $wooProduct) {
            return back()->with('error', 'No matching WordPress product found.');
        }

        $product->update([
            'status' => 'listed'
        ]);


        // update listing to the name used on the listing. 
        if (!empty($wooProduct->name)) {
            $link->listing->update([
                'title' => $wooProduct->name,
                'live_date' => $wooProduct->date_created,
                // 'price' => $wooProduct->price
            ]);
        }

        $link->update([
            'external_id' => $wooProduct->id,
            'status' => $wooProduct->status ?? 'linked',
            'payload' => [
                'matched_by' => $product->sku ? 'sku_or_title' : 'title',
                'wordpress_product' => [
                    'id' => $wooProduct->id,
                    'name' => $wooProduct->name ?? null,
                    'sku' => $wooProduct->sku ?? null,
                    'status' => $wooProduct->status ?? null,
                    'permalink' => $wooProduct->permalink ?? null,
                ],
            ],
        ]);

        return back()->with('success', 'WordPress product linked successfully.');
    }

    public function updateSyncImages(Request $request, ListingPlatformLink $link)
    {
        $validated = $request->validate([
            'sync_images' => ['required', 'boolean'],
        ]);

        $link->update([
            'sync_images' => $validated['sync_images'],
        ]);

        return back()->with('success', 'Image sync setting updated.');
    }
}
