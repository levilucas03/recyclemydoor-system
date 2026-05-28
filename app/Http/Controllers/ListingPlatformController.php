<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ListingPlatform;
use App\Services\WordPress\WooCommerceService;



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
        return Inertia::render('listing-platforms/Edit', [
            'platform' => $listingPlatform,
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
}
