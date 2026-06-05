<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingPlatform;
use App\Models\ListingPlatformLink;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ListingController extends Controller
{
    public function index()
    {
        $listings = Listing::with(['products.primaryImage', 'platformLinks.platform'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return Inertia::render('listing/Index', [
            'listings' => $listings,
        ]);
    }

    public function create(Request $request)
    {

        $selectedProductId = $request->integer('product_id');

        $products = Product::with(['primaryImage'])
            ->whereNull('listing_id')
            ->where('user_id', auth()->id())
            ->get();

        $platforms = ListingPlatform::where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('listing/Create', [
            'products' => $products,
            'platforms' => $platforms,
            'selectedProductId' => $selectedProductId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'product_id' => 'nullable|exists:products,id',
            'platform_ids' => 'nullable|array',
            'platform_ids.*' => 'exists:listing_platforms,id',
        ]);

        $listing = Listing::create([
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? '',
            'user_id' => auth()->id()
        ]);



        // Assign selected products to the new listing
        if (!empty($validated['product_id'])) {
            Product::where('id', $validated['product_id'])
                ->where('user_id', auth()->id())
                ->update(['listing_id' => $listing->id]);
        }

        foreach ($validated['platform_ids'] ?? [] as $platformId) {
            ListingPlatformLink::firstOrCreate([
                'listing_id' => $listing->id,
                'listing_platform_id' => $platformId,
            ], [
                'status' => 'draft',
            ]);
        }

        return redirect()->route('listings.edit', $listing)
            ->with('success', 'Listing created.');
    }

    public function edit(Listing $listing)
    {
        abort_unless($listing->user_id === auth()->id(), 403);

        $listing->load([
            'products.primaryImage',
            'platformLinks.platform',
        ]);

        $products = Product::with('primaryImage')
            ->where('user_id', auth()->id())
            ->where(function ($query) use ($listing) {
                $query->whereNull('listing_id')
                    ->orWhere('listing_id', $listing->id);
            })
            ->get();

        $platforms = ListingPlatform::where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('listing/Edit', [
            'listing' => $listing,
            'products' => $products,
            'platforms' => $platforms,
            'selected_platform_ids' => $listing->platformLinks
                ->pluck('listing_platform_id')
                ->values(),
        ]);
    }

    public function update(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'product_id' => 'nullable|exists:products,id',
            'platform_ids' => 'nullable|array',
            'platform_ids.*' => 'exists:listing_platforms,id',
        ]);

        $listing->update([
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? '',
        ]);

        Product::where('listing_id', $listing->id)
            ->where('user_id', auth()->id())
            ->update(['listing_id' => null]);

        if (!empty($validated['product_id'])) {
            Product::where('id', $validated['product_id'])
                ->where('user_id', auth()->id())
                ->update(['listing_id' => $listing->id]);
        }

        $selectedPlatformIds = collect($validated['platform_ids'] ?? []);

        ListingPlatformLink::where('listing_id', $listing->id)
            ->whereNotIn('listing_platform_id', $selectedPlatformIds)
            ->whereNull('external_id')
            ->delete();

        foreach ($selectedPlatformIds as $platformId) {
            ListingPlatformLink::firstOrCreate([
                'listing_id' => $listing->id,
                'listing_platform_id' => $platformId,
            ], [
                'status' => 'draft',
            ]);
        }

        return redirect()
            ->route('listings.edit', $listing)
            ->with('success', 'Listing updated.');
    }



    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:listings,id',
        ]);

        Listing::whereIn('id', $request->ids)->delete();

        return redirect()->route('listings.index')->with('success', 'Selected listings deleted successfully.');
    }

}
