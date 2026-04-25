<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ListingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $listings = Listing::where('user_id', $user->id)->paginate(10);

        return Inertia::render('listing/Index', [
            'listings' => $listings,
        ]);
    }

    public function create()
    {
        $products = Product::whereNull('listing_id')->where('user_id', auth()->id())->get();

        return Inertia::render('listing/Create', [
            'products' => $products
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $listing = Listing::create([
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? '',
            'user_id' => auth()->id()
        ]);



        // Assign selected products to the new listing
        Product::where('id', $validated['product_id'])
            ->update(['listing_id' => $listing->id]);

        return redirect()->route('listings.index')->with('success', 'Listing created.');
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
