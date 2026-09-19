<?php 

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Contact;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Services\XeroService;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */

        $search = $request->search;
        $status = $request->status;
        $category = $request->category;
        $startDate = $request->start_date;
        $endDate = $request->end_date;


        /*
        |--------------------------------------------------------------------------
        | FILTER FUNCTION
        |--------------------------------------------------------------------------
        |
        | We use this in a few places:
        |
        | 1. Main sales query
        | 2. Status counts
        | 3. Category counts
        |
        | This prevents us repeating all the search/date logic.
        |
        */

        $applyFilters = function (
            $query,
            $includeStatus = true,
            $includeCategory = true
        ) use (
            $search,
            $status,
            $category,
            $startDate,
            $endDate
        ) {

            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */

            if ($search) {

                $query->where(function ($q) use ($search) {

                    /*
                    |--------------------------------------------------------------------------
                    | SALE ID
                    |--------------------------------------------------------------------------
                    */

                    $q->where(
                        'id',
                        'like',
                        "%{$search}%"
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | CUSTOMER
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhereHas(
                        'contact',
                        function ($contact) use ($search) {

                            $contact
                                ->where(
                                    'first_name',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'last_name',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhereRaw(
                                    "CONCAT(first_name, ' ', last_name) LIKE ?",
                                    ["%{$search}%"]
                                )

                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'telephone',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'mobile',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'postcode',
                                    'like',
                                    "%{$search}%"
                                );

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | PRODUCT
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhereHas(
                        'items.product',
                        function ($product) use ($search) {

                            $product
                                ->where(
                                    'sku',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'title',
                                    'like',
                                    "%{$search}%"
                                );

                        }
                    );

                });

            }


            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            if ($includeStatus && $status) {

                $query->where(
                    'status',
                    $status
                );

            }


            /*
            |--------------------------------------------------------------------------
            | CATEGORY
            |--------------------------------------------------------------------------
            */

            if ($includeCategory && $category) {
                $query->whereHas(
                    'items.product.categories',
                    function ($categoryQuery) use ($category) {

                        $categoryQuery->where(
                            'categories.id',
                            $category
                        );

                    }
                );
            }       


            /*
            |--------------------------------------------------------------------------
            | DATE FROM
            |--------------------------------------------------------------------------
            */

            if ($startDate) {

                $query->whereDate(
                    'invoice_date',
                    '>=',
                    $startDate
                );

            }


            /*
            |--------------------------------------------------------------------------
            | DATE TO
            |--------------------------------------------------------------------------
            */

            if ($endDate) {

                $query->whereDate(
                    'invoice_date',
                    '<=',
                    $endDate
                );

            }


            return $query;
        };


        /*
        |--------------------------------------------------------------------------
        | MAIN SALES QUERY
        |--------------------------------------------------------------------------
        */

        $salesQuery = Sale::query()
            ->with([

                'contact',

                'source',

                'items.product' => function ($query) {

                    $query->with([

                        'primaryImage',

                        'categories',

                        'prices',

                        'partAllocations',

                    ]);

                },

            ]);


        /*
        |--------------------------------------------------------------------------
        | APPLY ALL FILTERS
        |--------------------------------------------------------------------------
        */

        $applyFilters(
            $salesQuery,
            true,
            true
        );


        /*
        |--------------------------------------------------------------------------
        | SUMMARY DATA
        |--------------------------------------------------------------------------
        |
        | Get all matching sales BEFORE pagination.
        |
        | This means the figures at the top represent the entire filtered
        | result rather than only the current page.
        |
        */

        $summarySales = (clone $salesQuery)
            ->get();
            


        /*

        
        |--------------------------------------------------------------------------
        | REVENUE
        |--------------------------------------------------------------------------
        */

        $totalRevenue = (float) $summarySales
            ->sum('total_amount');


        /*
        |--------------------------------------------------------------------------
        | PRODUCT SALE ITEMS
        |--------------------------------------------------------------------------
        */

        $productItems = $summarySales

            ->flatMap(function ($sale) {

                return $sale->items;

            })

            ->filter(function ($item) {

                /*
                |--------------------------------------------------------------------------
                | Include product and other
                |--------------------------------------------------------------------------
                |
                | We previously found that some of your product-type sale lines
                | can also be stored as "other".
                |
                | However, for cost calculations below, we still require an
                | attached product.
                |
                */

                return in_array(
                    $item->type,
                    ['product', 'other']
                );

            });


        /*
        |--------------------------------------------------------------------------
        | ACTUAL PRODUCT ITEMS
        |--------------------------------------------------------------------------
        |
        | Only lines with an attached Product can contribute product cost,
        | refurb cost and product quantity.
        |
        */

        $attachedProductItems = $productItems
            ->filter(function ($item) {

                return $item->product !== null;

            });


        /*
        |--------------------------------------------------------------------------
        | PRODUCTS SOLD
        |--------------------------------------------------------------------------
        */

        $productsSold = $attachedProductItems
            ->sum(function ($item) {

                return (float) ($item->qty ?? 1);

            });


        /*
        |--------------------------------------------------------------------------
        | PRODUCT PURCHASE COST
        |--------------------------------------------------------------------------
        */

        $productCost = $attachedProductItems
            ->sum(function ($item) {

                $product = $item->product;

                if (!$product) {
                    return 0;
                }


                /*
                |--------------------------------------------------------------------------
                | PURCHASE PRICE
                |--------------------------------------------------------------------------
                */

                $purchasePrice = (float) optional(
                    $product
                        ->prices
                        ->firstWhere(
                            'type',
                            'purchase'
                        )
                )->price;


                /*
                |--------------------------------------------------------------------------
                | QUANTITY
                |--------------------------------------------------------------------------
                */

                $qty = (float) (
                    $item->qty ?? 1
                );


                return $purchasePrice * $qty;

            });


        /*
        |--------------------------------------------------------------------------
        | REFURB / PART COST
        |--------------------------------------------------------------------------
        */

        $refurbCost = $attachedProductItems
            ->sum(function ($item) {

                $product = $item->product;

                if (!$product) {
                    return 0;
                }


                /*
                |--------------------------------------------------------------------------
                | PARTS ALLOCATED TO PRODUCT
                |--------------------------------------------------------------------------
                */

                $partsCost = (float) $product
                    ->partAllocations
                    ->sum('cost_allocated');


                /*
                |--------------------------------------------------------------------------
                | Quantity
                |--------------------------------------------------------------------------
                |
                | Normally your products are unique physical stock items, so qty
                | will usually be 1.
                |
                */

                $qty = (float) (
                    $item->qty ?? 1
                );


                return $partsCost * $qty;

            });


        /*
        |--------------------------------------------------------------------------
        | TOTAL COST
        |--------------------------------------------------------------------------
        */

        $totalCost =
            $productCost +
            $refurbCost;


        /*
        |--------------------------------------------------------------------------
        | GROSS PROFIT
        |--------------------------------------------------------------------------
        */

        $grossProfit =
            $totalRevenue -
            $totalCost;


        /*
        |--------------------------------------------------------------------------
        | MARGIN
        |--------------------------------------------------------------------------
        */

        $margin = $totalRevenue > 0

            ? (
                $grossProfit /
                $totalRevenue
            ) * 100

            : 0;


        /*
        |--------------------------------------------------------------------------
        | STATUS COUNTS
        |--------------------------------------------------------------------------
        |
        | Important:
        |
        | We apply:
        | - search
        | - category
        | - dates
        |
        | But NOT the selected status.
        |
        | This means if you click "Complete", the sidebar still shows the
        | number of Draft / Cancelled / etc sales.
        |
        */

        $statusQuery = Sale::query();


        $applyFilters(
            $statusQuery,
            false,
            true
        );


        $statusCounts = $statusQuery

            ->selectRaw(
                'status, COUNT(*) as total'
            )

            ->groupBy('status')

            ->pluck(
                'total',
                'status'
            );


        /*
        |--------------------------------------------------------------------------
        | CATEGORY COUNTS
        |--------------------------------------------------------------------------
        |
        | Here we apply:
        |
        | - search
        | - status
        | - dates
        |
        | But NOT category.
        |
        | Therefore the user can still see all available category counts even
        | when one category has been selected.
        |
        */

        $categorySalesQuery = Sale::query()
            ->with([
                'items.product.categories'
            ]);


        $applyFilters(
            $categorySalesQuery,
            true,
            false
        );


        $categorySales = $categorySalesQuery
            ->get();


        /*
        |--------------------------------------------------------------------------
        | BUILD CATEGORY COUNTS
        |--------------------------------------------------------------------------
        */

        $categories = $categorySales

            ->flatMap(function ($sale) {

                return $sale->items;

            })

            /*
            |--------------------------------------------------------------------------
            | Must actually have a product
            |--------------------------------------------------------------------------
            */

            ->filter(function ($item) {

                return $item->product !== null;

            })

            /*
            |--------------------------------------------------------------------------
            | Turn each SaleItem into category entries
            |--------------------------------------------------------------------------
            |
            | A product may belong to more than one category.
            |
            */

            ->flatMap(function ($item) {

                return $item->product->categories->map(
                    function ($category) use ($item) {

                        return [

                            'category' => $category,

                            'qty' => (int) (
                                $item->qty ?? 1
                            ),

                        ];

                    }
                );

            })

            /*
            |--------------------------------------------------------------------------
            | Group by category
            |--------------------------------------------------------------------------
            */

            ->groupBy(function ($item) {

                return $item['category']->id;

            })

            /*
            |--------------------------------------------------------------------------
            | Build sidebar data
            |--------------------------------------------------------------------------
            */

            ->map(function ($items) {

                $category = $items
                    ->first()['category'];

                return [

                    'id' => $category->id,

                    'name' => $category->name,

                    'count' => $items->sum('qty'),

                ];

            })

            ->sortByDesc('count')

            ->values();


        /*
        |--------------------------------------------------------------------------
        | PAGINATED SALES
        |--------------------------------------------------------------------------
        */

        $sales = $salesQuery

            /*
            |--------------------------------------------------------------------------
            | Sort by actual invoice date
            |--------------------------------------------------------------------------
            */

            ->orderByDesc('invoice_date')

            /*
            |--------------------------------------------------------------------------
            | Secondary sort
            |--------------------------------------------------------------------------
            */

            ->orderByDesc('id')

            ->paginate(20)

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Sales/Index',
            [

                /*
                |--------------------------------------------------------------------------
                | SALES
                |--------------------------------------------------------------------------
                */

                'sales' => $sales,


                /*
                |--------------------------------------------------------------------------
                | SUMMARY CARDS
                |--------------------------------------------------------------------------
                */

                'summary' => [

                    /*
                    |--------------------------------------------------------------------------
                    | Revenue
                    |--------------------------------------------------------------------------
                    */

                    'revenue' => round(
                        $totalRevenue,
                        2
                    ),


                    /*
                    |--------------------------------------------------------------------------
                    | Number of sales
                    |--------------------------------------------------------------------------
                    */

                    'sales_count' => $summarySales
                        ->count(),


                    /*
                    |--------------------------------------------------------------------------
                    | Products sold
                    |--------------------------------------------------------------------------
                    */

                    'products_sold' => (int) $productsSold,


                    /*
                    |--------------------------------------------------------------------------
                    | Stock purchase cost
                    |--------------------------------------------------------------------------
                    */

                    'product_cost' => round(
                        $productCost,
                        2
                    ),


                    /*
                    |--------------------------------------------------------------------------
                    | Refurbishment / parts
                    |--------------------------------------------------------------------------
                    */

                    'refurb_cost' => round(
                        $refurbCost,
                        2
                    ),


                    /*
                    |--------------------------------------------------------------------------
                    | Total actual cost
                    |--------------------------------------------------------------------------
                    */

                    'total_cost' => round(
                        $totalCost,
                        2
                    ),


                    /*
                    |--------------------------------------------------------------------------
                    | Gross profit
                    |--------------------------------------------------------------------------
                    */

                    'gross_profit' => round(
                        $grossProfit,
                        2
                    ),


                    /*
                    |--------------------------------------------------------------------------
                    | Margin
                    |--------------------------------------------------------------------------
                    */

                    'margin' => round(
                        $margin,
                        1
                    ),

                ],


                /*
                |--------------------------------------------------------------------------
                | SIDEBAR STATUS COUNTS
                |--------------------------------------------------------------------------
                */

                'statusCounts' => $statusCounts,


                /*
                |--------------------------------------------------------------------------
                | SIDEBAR CATEGORY COUNTS
                |--------------------------------------------------------------------------
                */

                'categories' => $categories,


                /*
                |--------------------------------------------------------------------------
                | ACTIVE FILTERS
                |--------------------------------------------------------------------------
                */

                'filters' => [

                    'search' => $search,

                    'status' => $status,

                    'category' => $category,

                    'start_date' => $startDate,

                    'end_date' => $endDate,

                ],

            ]
        );
    }

    public function create()
    {

        return Inertia::render('Sales/Create', [
            'statusOptions' => SaleStatus::options(),
            'sources' => Source::select('id', 'name')->get(),
        ]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'contact' => 'nullable|array',
            'contact_id' => 'nullable|exists:contacts,id',
            'contact.first_name' => 'required_without:contact_id|string',
            'contact.last_name' => 'nullable|string',
            'contact.email' => 'nullable|email',
            'contact.mobile' => 'nullable|string',
            'contact.type' => 'required_without:contact_id|in:general_public,supplier,company',

            'status' => 'string',
            'source_id' => 'required',
            
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string',
            'reference' => ['nullable', 'string', 'max:255'],
            'is_private' => 'nullable|boolean',
        ]);


        $contactData = $request['contact'] ?? [];

        $contactData['address_1'] = $request['address_1'] ?? null;
        $contactData['address_2'] = $request['address_2'] ?? null;
        $contactData['town_city'] = $request['town_city'] ?? null;
        $contactData['postcode'] = $request['postcode'] ?? null;
        $contactData['invoice_address_1'] = $request['address_1'] ?? null;
        $contactData['invoice_address_2'] = $request['address_2'] ?? null;
        $contactData['invoice_town_city'] = $request['town_city'] ?? null;
        $contactData['invoice_postcode'] = $request['postcode'] ?? null;

        // Contact logic
        if (!empty($request['contact_id'])) {
            $contact = Contact::findOrFail($request['contact_id']);

        } else {
            $contact = Contact::create($contactData);
            $request['contact_id'] = $contact->id;
        }


        DB::transaction(function () use ($request) {

            $sale = Sale::create([
                'contact_id' => $request['contact_id'],
                'user_id' => auth()->id(),
                'status' => $request->status,
                'invoice_date' => $request->invoice_date,
                'notes' => $request->notes,
                'source' => $request->source,
                'total_amount' => 0,
                'total_vat_amount' => 0,
                'deliver_address_1' => $request['address_1'] ?? null,
                'deliver_address_2' => $request['address_2'] ?? null,
                'deliver_town_city' => $request['town_city'] ?? null,
                'deliver_postcode' => $request['postcode'] ?? null,
                'source_id' => $request->source_id,
                'is_private' => $request->boolean('is_private'),
            ]);

            $total = 0;
            $vatTotal = 0;

            foreach ($request->items as $item) {

                $lineTotal = ($item['price'] * $item['qty']) - ($item['discount'] ?? 0);
                $vat = $item['vat_amount'] ?? 0;

                $sale->items()->create([
                    'type' => $item['type'] ?? 'product',
                    'product_id' => $item['product_id'] ?? null,
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'discount' => $item['discount'] ?? 0,
                    'vat_amount' => $vat,
                    'total' => $lineTotal,
                    'account_code' => $item['account_code'] ?? null,
                    'note' => $item['note'] ?? null,
                    
                ]);

                if (!empty($item['product_id'])) {

                    $product = Product::find($item['product_id']);

                    if ($product) {

                        // --------------------
                        // SAVE SOLD PRICE
                        // --------------------
                        ProductPrice::create([
                            'product_id' => $product->id,
                            'type' => 'sold',
                            'price' => $item['price'],
                        ]);

                        // --------------------
                        // UPDATE STOCK
                        // --------------------
                        $product->qty = max(0, $product->qty - $item['qty']);

                        // --------------------
                        // MARK SOLD
                        // --------------------
                        if ($product->qty <= 0) {

                            $product->qty = 0;
                            $product->status = 'sold';
                        }

                        $product->save();
                    }
                }

                $total += $lineTotal;
                $vatTotal += $vat;
            }

            $sale->update([
                'reference' => $sale->generateReference(),
                'total_amount' => $total,
                'total_vat_amount' => $vatTotal,
            ]);
        });

        return redirect()->route('sales.index')
            ->with('success', 'Sale created successfully');
    }

    public function show(Sale $sale)
    {
        $sale->load('items', 'contact');

        return Inertia::render('Sales/Show', [
            'sale' => $sale
        ]);
    }

    public function edit(Sale $sale)
    {
        $sale->load([
            'contact',
            'items.product.primaryImage',
            'source'
        ]);


        return Inertia::render('Sales/Edit', [
            'sale' => $sale,
            'sources' => Source::select('id', 'name')->get(),
            'statusOptions' => SaleStatus::options(),
            'sources' => Source::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, Sale $sale)
    {

        $request->validate([
            'contact' => 'nullable|array',
            'contact_id' => 'nullable|exists:contacts,id',
            'contact.first_name' => 'required_without:contact_id|string',
            'contact.last_name' => 'nullable|string',
            'contact.email' => 'nullable|email',
            'contact.mobile' => 'nullable|string',
            'contact.type' => 'required_without:contact_id|in:general_public,supplier,company',

            'status' => 'string',
            'source_id' => 'required',
            
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string',
            'reference' => ['nullable', 'string', 'max:255'],
            'is_private' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $sale) {

            // --------------------
            // CONTACT LOGIC
            // --------------------
            $contactId = $request->contact_id;

            if (!$contactId) {

                $contactData = $request->contact ?? [];

                $contactData = array_merge($contactData, [
                    'address_1' => $request->address_1,
                    'address_2' => $request->address_2,
                    'town_city' => $request->town_city,
                    'postcode' => $request->postcode,

                    'invoice_address_1' => $request->address_1,
                    'invoice_address_2' => $request->address_2,
                    'invoice_town_city' => $request->town_city,
                    'invoice_postcode' => $request->postcode,
                ]);

                $contact = Contact::create($contactData);
                $contactId = $contact->id;
            }

            // --------------------
            // UPDATE SALE HEADER
            // --------------------
            $sale->update([
                'contact_id' => $contactId,

                'invoice_date' => $request->invoice_date,
                'notes' => $request->notes,

                'status' => $request->status,
                'source_id' => $request->source_id,

                'deliver_address_1' => $request->address_1,
                'deliver_address_2' => $request->address_2,
                'deliver_town_city' => $request->town_city,
                'deliver_postcode' => $request->postcode,
                'is_private' => $request->boolean('is_private'),
            ]);

            // dd($sale->items);

            foreach ($sale->items as $oldItem) {

                if ($oldItem->product_id) {

                    $product = Product::find($oldItem->product_id);

                    if ($product) {

                        // restore stock
                        $product->qty += $oldItem->qty;

                        // restore status
                        if ($product->qty > 0) {
                            $product->status = 'stored';
                        }

                        $product->save();

                        // remove old sold prices
                        ProductPrice::query()
                            ->where('product_id', $product->id)
                            ->where('type', 'sold')
                            // ->where('price', $oldItem->price)
                            ->delete();
                    }
                }
            }

            // --------------------
            // RESET ITEMS
            // --------------------
            $sale->items()->delete();

            $total = 0;
            $vatTotal = 0;

            // --------------------
            // RE-CREATE ITEMS
            // --------------------
            foreach ($request->items as $item) {

                $lineTotal = ($item['price'] * $item['qty']) - ($item['discount'] ?? 0);
                $vat = $item['vat_amount'] ?? 0;

                $sale->items()->create([
                    'type' => $item['type'] ?? 'product',
                    'product_id' => $item['product_id'] ?? null,

                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,

                    'image' => $item['image'] ?? null,
                    'size' => $item['size'] ?? null,

                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'discount' => $item['discount'] ?? 0,
                    'vat_amount' => $vat,
                    'total' => $lineTotal,

                    'account_code' => $item['account_code'] ?? null,
                    'note' => $item['note'] ?? null,
                ]);

                if (!empty($item['product_id'])) {

                    $product = Product::find($item['product_id']);

                    if ($product) {

                        // --------------------
                        // SAVE SOLD PRICE
                        // --------------------
                        ProductPrice::create([
                            'product_id' => $product->id,
                            'type' => 'sold',
                            'price' => $item['price'],
                        ]);

                        // --------------------
                        // UPDATE STOCK
                        // --------------------
                        $product->qty = max(0, $product->qty - $item['qty']);

                        // --------------------
                        // MARK SOLD
                        // --------------------
                        if ($product->qty <= 0) {

                            $product->qty = 0;
                            $product->status = 'sold';
                        }

                        $product->save();
                    }
                }

                $total += $lineTotal;
                $vatTotal += $vat;
            }

            // --------------------
            // UPDATE TOTALS
            // --------------------
            $sale->update([
                'reference' => $sale->generateReference(),
                'total_amount' => $total,
                'total_vat_amount' => $vatTotal,
            ]);
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale updated successfully');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        return back()->with('success', 'Sale deleted');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:sales,id',
        ]);

        Sale::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Selected sales deleted');
    }

    public function pushToXero(Sale $sale, XeroService $xero)
    {
        // dd($sale);
        $sale->load([
            'items.product',
            'contact',
        ]);

        // try {

            $invoice = $xero->createSaleInvoice($sale);

            $sale->xero_id = $invoice->getInvoiceId();
            $sale->save();

            return back()->with('success', 'Sale pushed to Xero');

        // } catch (\Throwable $e) {

            return back()->with('error', $e->getMessage());

        // }
    }

    private function applySalesIndexFilters(
        $query,
        Request $request,
        bool $includeStatus = true,
        bool $includeCategory = true
    ) {

        $search = $request->search;

        $status = $request->status;

        $category = $request->category;

        $startDate = $request->start_date;

        $endDate = $request->end_date;


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        $query->when($search, function ($query) use ($search) {

            $query->where(function ($q) use ($search) {

                $q->where(
                    'id',
                    'like',
                    "%{$search}%"
                );


                $q->orWhereHas(
                    'contact',
                    function ($contact) use ($search) {

                        $contact
                            ->where(
                                'first_name',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhere(
                                'last_name',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhere(
                                'email',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhereRaw(
                                "CONCAT(first_name, ' ', last_name) LIKE ?",
                                ["%{$search}%"]
                            )

                            ->orWhere(
                                'telephone',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhere(
                                'mobile',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhere(
                                'postcode',
                                'like',
                                "%{$search}%"
                            );

                    }
                );


                $q->orWhereHas(
                    'items.product',
                    function ($product) use ($search) {

                        $product
                            ->where(
                                'sku',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhere(
                                'title',
                                'like',
                                "%{$search}%"
                            );

                    }
                );

            });

        });


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        if ($includeStatus) {

            $query->when(
                $status,
                fn ($query) =>
                    $query->where(
                        'status',
                        $status
                    )
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CATEGORY
        |--------------------------------------------------------------------------
        */

        if ($includeCategory) {

            $query->when(
                $category,
                function ($query) use ($category) {

                    $query->whereHas(
                        'items.product',
                        function ($product) use ($category) {

                            $product->where(
                                'category_id',
                                $category
                            );

                        }
                    );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | DATE
        |--------------------------------------------------------------------------
        */

        $query->when(
            $startDate,
            fn ($query) =>
                $query->whereDate(
                    'invoice_date',
                    '>=',
                    $startDate
                )
        );


        $query->when(
            $endDate,
            fn ($query) =>
                $query->whereDate(
                    'invoice_date',
                    '<=',
                    $endDate
                )
        );


        return $query;
    }

}