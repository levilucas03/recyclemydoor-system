<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Source;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Collection;
use App\Enums\PurchaseStatus;
use Illuminate\Validation\Rule;
use App\Services\XeroService;
use App\DataTransferObjects\Xero\CreatePurchasePayload;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = Purchase::with([
                'contact',
                'source',
                'products.prices',
            ])
            ->withCount('products')
            ->orderBy('purchase_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $purchases->getCollection()->transform(function ($purchase) {

            $soldRevenue = $purchase->products->sum(function ($product) {
                return (float) optional(
                    $product->prices->firstWhere('type', 'sold')
                )->price;
            });

            $purchaseTotal = (float) $purchase->total_amount;

            $profit = $soldRevenue - $purchaseTotal;

            $roi = $purchaseTotal > 0
                ? round(($soldRevenue / $purchaseTotal) * 100, 1)
                : 0;

            $purchase->sold_revenue = $soldRevenue;
            $purchase->profit = $profit;
            $purchase->roi = $roi;

            $soldCount = $purchase->products->filter(function ($product) {
                return $product->prices->firstWhere('type', 'sold');
            })->count();

            $purchase->sold_count = $soldCount;

            return $purchase;
        });

        return Inertia::render('purchase/Index', [
            'purchases' => $purchases,
            'statusOptions' => PurchaseStatus::options(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
   
    public function create()
    {
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->get();

        $materials = Attribute::whereHas('group', fn($q) => $q->where('slug', 'material'))->get();
        $colours = Attribute::whereHas('group', fn($q) => $q->where('slug', 'colour'))->get();

        // dd(PurchaseStatus::options());

        return Inertia::render('purchase/Create', [
            'categories' => $categories,
            'materials' => $materials,
            'colours' => $colours,
            'statusOptions' => PurchaseStatus::options(),
            'sources' => Source::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact' => 'nullable|array',
            'contact_id' => 'nullable|exists:contacts,id',
            'contact.first_name' => 'required_without:contact_id|string',
            'contact.last_name' => 'nullable|string',
            'contact.email' => 'nullable|email',
            'contact.mobile' => 'nullable|string',
            'contact.type' => 'required_without:contact_id|in:general_public,supplier,company',

            'purchase_date' => 'required|date',
            'status' => 'required|string',
            'notes' => 'nullable',
            'collection_notes' => 'nullable',
            'driver_notes' => 'nullable',
            'address_1' => 'nullable|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'town_city' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'source_id' => 'required',

            'products' => 'required|array|min:0',
            'products.*.title' => 'required|string',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.width' => 'nullable|numeric',
            'products.*.height' => 'nullable|numeric',
            'products.*.depth' => 'nullable|numeric',
            'products.*.colour' => 'nullable|string',
            'products.*.type' => 'nullable|string',

            'products.*.category_ids' => 'nullable|array',
            'products.*.category_ids.*' => 'exists:categories,id',
            'products.*.material_id' => 'nullable|exists:attributes,id',
            'products.*.colour_id' => 'nullable|exists:attributes,id',
        ]);

        $contactData = $data['contact'] ?? [];

        $contactData['address_1'] = $data['address_1'] ?? null;
        $contactData['address_2'] = $data['address_2'] ?? null;
        $contactData['town_city'] = $data['town_city'] ?? null;
        $contactData['postcode'] = $data['postcode'] ?? null;
        $contactData['invoice_address_1'] = $data['address_1'] ?? null;
        $contactData['invoice_address_2'] = $data['address_2'] ?? null;
        $contactData['invoice_town_city'] = $data['town_city'] ?? null;
        $contactData['invoice_postcode'] = $data['postcode'] ?? null;


        // Contact logic
        if (!empty($data['contact_id'])) {
            $contact = Contact::findOrFail($data['contact_id']);

        } else {
            $contact = Contact::create($contactData);
        }

        // Create purchase
        $purchase = Purchase::create([
            'contact_id' => $contact->id,
            'user_id' => auth()->id(),
            'status' => $data['status'],
            'purchase_date' => $data['purchase_date'],
            'total_amount' => collect($data['products'])->sum('price'),
            'collection_address_1' => $data['address_1'] ?? null,
            'collection_address_2' => $data['address_2'] ?? null,
            'collection_town_city' => $data['town_city'] ?? null,
            'collection_postcode' => $data['postcode'] ?? null,
            'notes' => $data['notes'] ?? null,
            'driver_notes' => $data['driver_notes'] ?? null,
            'collection_notes' => $data['collection_notes'] ?? null,
            'source_id' => $request->source_id,
        ]);

        // Create products
        foreach ($data['products'] as $productData) {

            $product = $purchase->products()->create([
                'title' => $productData['title'],
                'width' => $productData['width'] ?? null,
                'height' => $productData['height'] ?? null,
                'depth' => $productData['depth'] ?? null,
                'colour' => $productData['colour'] ?? null,
                'type' => $productData['type'] ?? null,
                'user_id' => auth()->id(),
            ]);

            // attach categories
            if (!empty($productData['category_ids'])) {
                $product->categories()->sync($productData['category_ids']);
            }

            $attributeIds = array_filter([
                $productData['material_id'] ?? null,
                $productData['colour_id'] ?? null,
            ]);

            $product->attributes()->sync($attributeIds);

            // Create purchase price
            $product->prices()->create([
                'type' => 'purchase',
                'price' => $productData['price'],
            ]);
        }

        // IMPORTANT: redirect (NOT JSON)
        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $purchase->load('contact', 'products');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        $purchase->load([
            'contact',
            'products.categories',
            'products.attributes.group',
            'products.prices',
            'products.primaryImage',
            'source',
        ]);

        $categories = Category::whereNull('parent_id')->with('children')->get();
        $materials = Attribute::whereHas('group', fn ($q) => $q->where('slug', 'material'))->get();
        $colours = Attribute::whereHas('group', fn ($q) => $q->where('slug', 'colour'))->get();

        return Inertia::render('purchase/Edit', [
            'purchase' => $purchase,
            'categories' => $categories,
            'materials' => $materials,
            'colours' => $colours,
            'statusOptions' => PurchaseStatus::options(),
            'sources' => Source::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {

    
        $data = $request->validate([
            'contact_id' => 'nullable|exists:contacts,id',

            'contact.first_name' => 'required_without:contact_id|string',
            'contact.last_name' => 'nullable|string',
            'contact.email' => 'nullable|email',
            'contact.mobile' => 'nullable|string',
            'contact.type' => 'required_without:contact_id|in:general_public,supplier,company',

            'purchase_date' => 'required|date',
            'status' => 'string',

            'address_1' => 'nullable|string',
            'address_2' => 'nullable|string',
            'town_city' => 'nullable|string',
            'postcode' => 'nullable|string',

            'products' => 'required|array|min:1',
            'products.*.id' => 'nullable|exists:products,id',
            'products.*.title' => 'required|string',
            'products.*.width' => 'nullable|numeric',
            'products.*.height' => 'nullable|numeric',
            'products.*.price' => 'required|numeric|min:0',

            'products.*.category_ids' => 'nullable|array',
            'products.*.category_ids.*' => 'exists:categories,id',

            'products.*.material_id' => 'nullable|exists:attributes,id',
            'products.*.colour_id' => 'nullable|exists:attributes,id',

            'notes' => 'nullable',
            'collection_notes' => 'nullable',
            'driver_notes' => 'nullable',
            'source_id' => 'required'
        ]);


        $contactData = $data['contact'] ?? [];

        // ------------------------
        // CONTACT
        // ------------------------
       if (!empty($data['contact_id'])) {
            $contact = Contact::findOrFail($data['contact_id']);

        } else {
            $contact = Contact::create($contactData);
        }

        // ------------------------
        // UPDATE PURCHASE
        // ------------------------
        $purchase->update([
            'contact_id' => $contact->id,
            'purchase_date' => $data['purchase_date'],
            'status' => $data['status'],
            'collection_address_1' => $data['address_1'] ?? null,
            'collection_address_2' => $data['address_2'] ?? null,
            'collection_town_city' => $data['town_city'] ?? null,
            'collection_postcode' => $data['postcode'] ?? null,
            'collection_country' => $data['country'] ?? null,
            'total_amount' => collect($data['products'])->sum('price'),
            'notes' => $data['notes'] ?? null,
            'driver_notes' => $data['driver_notes'] ?? null,
            'collection_notes' => $data['collection_notes'] ?? null,
            'source_id' => $data['source_id'] ?? null,
        ]);

        // ------------------------
        // EXISTING IDS
        // ------------------------
        $existingProductIds = $purchase->products()->pluck('id')->toArray();
        $incomingProducts = collect($data['products']);

        // ------------------------
        // LOOP PRODUCTS
        // ------------------------
        foreach ($incomingProducts as $productData) {

            // ------------------------
            // UPDATE EXISTING
            // ------------------------
            if (!empty($productData['id'])) {

                $product = $purchase->products()->find($productData['id']);

                if ($product) {

                    $product->update([
                        'title' => $productData['title'],
                        'width' => $productData['width'] ?? null,
                        'height' => $productData['height'] ?? null,
                    ]);

                    // 🔥 PRICE UPDATE
                    $price = $product->prices()
                        ->where('type', 'purchase')
                        ->first();

                    if ($price) {
                        $price->update([
                            'price' => $productData['price'],
                        ]);
                    } else {
                        $product->prices()->create([
                            'type' => 'purchase',
                            'price' => $productData['price'],
                        ]);
                    }

                    // 🔥 ATTRIBUTES (material + colour)
                    $attributeIds = array_filter([
                        $productData['material_id'] ?? null,
                        $productData['colour_id'] ?? null,
                    ]);

                    $product->attributes()->sync($attributeIds);

                    // 🔥 CATEGORIES
                    $product->categories()->sync($productData['category_ids'] ?? []);
                }
            }

            // ------------------------
            // CREATE NEW
            // ------------------------
            else {

                $product = $purchase->products()->create([
                    'title' => $productData['title'],
                    'width' => $productData['width'] ?? null,
                    'height' => $productData['height'] ?? null,
                    'user_id' => auth()->id(),
                ]);

                // PRICE
                $product->prices()->create([
                    'type' => 'purchase',
                    'price' => $productData['price'],
                ]);

                // ATTRIBUTES
                $attributeIds = array_filter([
                    $productData['material_id'] ?? null,
                    $productData['colour_id'] ?? null,
                ]);

                $product->attributes()->sync($attributeIds);

                // CATEGORIES
                $product->categories()->sync($productData['category_ids'] ?? []);
            }
        }

        // ------------------------
        // DELETE REMOVED
        // ------------------------
        $incomingIds = $incomingProducts
            ->pluck('id')
            ->filter()
            ->toArray();

        $productsToDelete = array_diff($existingProductIds, $incomingIds);

        $purchase->products()
            ->whereIn('id', $productsToDelete)
            ->delete();

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:purchases,id',
        ]);

        Purchase::whereIn('id', $request->ids)->delete();

        return redirect()->route('purchases.index')->with('success', 'Selected purchases deleted successfully.');
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:purchases,id',
            'status' => 'required|string',
        ]);

        // optional: validate enum
        if (!PurchaseStatus::tryFrom($request->status)) {
            return back()->with('error', 'Invalid status');
        }

        Purchase::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        return back()->with('success', 'Status updated');
    }

    public function pushToXero(Purchase $purchase, XeroService $xero)
    {
        $purchase->load(['products', 'contact']);

    //    dd($purchase->products->count());

        // try {
            $invoices = $xero->createPurchase($purchase);

            $purchase->xero_id = $invoices[0]->getInvoiceId();
            $purchase->save();

            return back()->with('success', 'Sent to Xero');

        // } catch (\Throwable $e) {
        //     return back()->with('error', $e->getMessage());
        // }
    }
}
