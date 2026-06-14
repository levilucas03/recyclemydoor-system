<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Category;
use App\Models\Part;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Enums\ProductStatus;
use Illuminate\Validation\Rule;
use App\Models\ProductImage;
use App\Services\ImageOptimizerService;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

       $products = Product::query()
        ->where('user_id', $user->id)
        ->with([
            'categories.parent',
            'primaryImage',
            'listing',
            'listing.platformLinks',
        ])

        // Search
        ->when($request->search, function ($query, $search) {

            $query->where(function ($q) use ($search) {

                $q->where('title', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");

            });
        })

        // Status Filter
        ->when($request->status, function ($query, $status) {

            if ($status === 'not_sold') {

                $query->where('status', '!=', 'sold');

            } else {

                $query->where('status', $status);

            }

        })

        ->orderBy('created_at', 'desc')

        ->paginate(15)

        ->withQueryString();

        $stats = [
            'total' => Product::where('user_id', auth()->id())->count(),

            'listed' => Product::where('user_id', auth()->id())
                ->where('status', 'listed')
                ->count(),

            'pending' => Product::where('user_id', auth()->id())
                ->where('status', 'pending')
                ->count(),

            'sold' => Product::where('user_id', auth()->id())
                ->where('status', 'sold')
                ->count(),

            'not_sold' => Product::where('user_id', auth()->id())
                ->where('status', '!=', 'sold')
                ->count(),

            'not_sold_purchase_value' => ProductPrice::where('type', 'purchase')
                ->whereHas('product', function ($query) {
                    $query->where('user_id', auth()->id())
                        ->where('status', '!=', 'sold');
                })
                ->sum('price'),

            'in_stock' => Product::where('user_id', $user->id)
                ->where('qty', '>', 0)
                ->count(),

            'stock_value' => ProductPrice::where('type', 'purchase')
                ->whereHas('product', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                    ->where('status', '!=', 'sold');
                })
                ->sum('price'),
        ];

        return Inertia::render('product/Index', [
            'products' => $products,
            'stats' => $stats,
            // 👇 send filters back to Vue
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ]
        ]);
    }
// to do maybe have the attributes pulled in  as a function to clean this function up.
    public function create()
    {
        $brands = Attribute::whereHas('group', function ($q) {
            $q->where('slug', 'brand');
        })->get();
        $materials = Attribute::whereHas('group', function ($q) {
            $q->where('slug', 'material');
        })->get();
        $colours = Attribute::whereHas('group', function ($q) {
            $q->where('slug', 'colour');
        })->get();

        return Inertia::render('product/Create', [
            'brands' => $brands,
            'materials' => $materials,
            'colours' => $colours,
        ]);
    }

    public function edit(Product $product)
    {

        $product->load([
            'attributes.group',
            'categories',
            'purchase.contact',
            'primaryImage',
            'images',
            'prices',
            'configuration',
            'listing',
            'partAllocations.part',
            
        ]); 

        // Transform prices → key by type
        // $prices = $product->prices->keyBy('type')->map(function ($price) {
        //     return [
        //         'price' => $price->price,
        //     ];
        // })->toArray();

        $groups = ['brand', 'material', 'colour', 'condition', 'opening', 'configuration', 'traffic-door'];

        $product->loadAttributeGroupIds($groups);

        // dd($product); 

        $attributesByGroup = Attribute::groupedByGroupSlugs($groups);

        $brands = $attributesByGroup->get('brand', collect());
        $materials = $attributesByGroup->get('material', collect());
        $colours = $attributesByGroup->get('colour', collect());
        $conditions = $attributesByGroup->get('condition', collect());
        $openings = $attributesByGroup->get('opening', collect());
        $trafficDoors = $attributesByGroup->get('traffic-door', collect());

        // dd( $product->loadAttributeGroupIds($groups););
        
        $configurations = Attribute::whereHas('group', function ($q) {
            $q->where('slug', 'configuration');
        })
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        $parts = Attribute::whereHas('group', function ($q) {
            $q->where('slug', 'parts');
        })->get();

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->get();

        $statuses = collect(ProductStatus::cases())->map(fn ($s) => [
            'value' => $s->value,
            'label' => $s->label(),
        ]);


        // dd($product->getPrice('purchase'));
        


        return Inertia::render('product/Edit', [
            'product' => $product,
            'brands' => $brands,
            'materials' => $materials,
            'colours' => $colours,
            'conditions' => $conditions,
            'openings' => $openings,
            'configurations' => $configurations,
            'parts' => $parts,
            'categories' => $categories,
            'statuses' => $statuses,
            'trafficDoors' => $trafficDoors,
            'allocateParts' => Part::with('allocations')
                ->orderBy('name')
                ->get()
                ->map(fn ($part) => [
                    'id' => $part->id,
                    'name' => $part->name,
                    'sku' => $part->sku,
                    'unit_cost' => $part->unit_cost,
                    'total_quantity' => $part->total_quantity,
                    'available_quantity' => $part->total_quantity - $part->allocations->sum('quantity_used'),
            ]),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string',
            'title' => 'nullable|string',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
            'depth' => 'required|numeric',

            'images.*' => ['nullable', 'image', 'max:10240'],

            'description' => 'nullable|string',
            'notes' => 'nullable|string',

            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',

            'part_ids' => 'nullable|array',
            'part_ids.*' => 'exists:attributes,id',

            'brand_id' => 'nullable|exists:attributes,id',
            'material_id' => 'nullable|exists:attributes,id',
            'colour_id' => 'nullable|exists:attributes,id',
            'traffic_door_id' => 'nullable|exists:attributes,id',
            'condition_id' => 'nullable|exists:attributes,id',
            'opening_id' => 'nullable|exists:attributes,id',
            'configuration_id' => 'nullable|exists:attributes,id',
            'status' => ['required', Rule::in(array_column(ProductStatus::cases(), 'value'))],

            'website_price' => 'nullable|numeric',
            'sold_price' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'initial_price' => 'nullable|numeric',
        ]);


        $product->update([
            'sku' => $validated['sku'],
            'title' => $validated['title'] ?? null,
            'width' => $validated['width'],
            'height' => $validated['height'],
            'depth' => $validated['depth'],
            'status' => $validated['status'],
            'description' => $validated['description'],
        ]);



        $singleAttributes = array_filter([
            $request->brand_id,
            $request->material_id,
            $request->colour_id,
            $request->condition_id,
            $request->traffic_door_id,
            $request->opening_id,
            $request->configuration_id,
        ]);



        $this->savePrice($product, 'website', $request->website_price);
        $this->savePrice($product, 'sold', $request->sold_price);
        $this->savePrice($product, 'purchase', $request->purchase_price);
        $this->savePrice($product, 'initial', $request->initial_price);

        $parts = $request->part_ids ?? [];

        $product->attributes()->sync([
            ...$singleAttributes,
            ...$parts,
        ]);

        $product->categories()->sync($request->category_ids ?? []);

        if ($request->hasFile('image')) {

            // delete old primary image (optional but recommended)
            $oldImage = $product->images()->where('is_primary', true)->first();

            if ($oldImage) {
                Storage::disk('public')->delete($oldImage->path);
                $oldImage->delete();
            }

            // store new image
            $path = app(ImageOptimizerService::class)
                ->storeProductImage($request->file('image'));

            // save to DB
            $product->images()->create([
                'path' => $path,
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

       if ($request->hasFile('images')) {

            $currentMaxOrder = $product->images()->max('sort_order') ?? 0;

            foreach ($request->file('images') as $image) {

                // Optional duplicate detection
                $hash = md5_file($image->getRealPath());

                $exists = $product->images()
                    ->where('file_hash', $hash)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $path = app(ImageOptimizerService::class)
                    ->storeProductImage(
                        $image,
                        $product->title
                    );

                $product->images()->create([
                    'path' => $path,
                    'file_hash' => $hash,
                    'alt_text' => $product->title,
                    'sort_order' => ++$currentMaxOrder,
                    'is_primary' => ! $product->images()->exists(),
                ]);
            }
        }
    }

    protected function savePrice(Product $product, $type, $value)
    {
        if ($value === null) return;

        $product->prices()->updateOrCreate(
            ['type' => $type],
            ['price' => $value]
        );
    }
// to do: attributes saving have it in a seperate function.
    public function store(Request $request)
    {


        $validated = $request->validate([
            // 'sku' => 'required|string|max:255',
            'width' => 'nullable',
            'height' => 'nullable',
        ]);



        // $path = $request->file('image')->store('products', 's3');





        // Optional: make the file public
        // Storage::disk('s3')->setVisibility($path, 'public');

        // $url = Storage::disk('s3')->url($path);


        // $validated['user_id'] = auth()->id();


        $product = Product::create($validated);
        

        // Collect non-null attributes
        $attributes = array_filter([
            $request->brand_id,
            $request->material_id,
            $request->colour_id,
            $request->trafficDoor_id,
            $request->opening_id,
            $request->configuration_id
        ]);


        // Attach them to the product
        if (!empty($attributes)) {
            $product->attributes()->attach($attributes);
        }

        return redirect()->route('products.index')->with('success', 'product created successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->back()->with('success', 'product deleted successfully.');
    }
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        Product::whereIn('id', $request->ids)->delete();

        return redirect()->route('products.index')->with('success', 'Selected products deleted successfully.');
    }

    

//    public function upload(Request $request)
//    {
//        $request->validate([
//            'image' => 'required|image|max:2048',
//        ]);
//
//        $path = $request->file('image')->store('products', 's3');
//
//        // Make file publicly accessible (if needed)
//        \Storage::disk('s3')->setVisibility($path, 'public');
//
//        $url = \Storage::disk('s3')->url($path);
//
//        return response()->json(['url' => $url]);
//    }

}
