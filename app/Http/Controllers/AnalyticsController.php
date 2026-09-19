<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SaleItem;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Sale;
use App\Models\FuelLog;
use App\Models\Part;


class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | DATE RANGE
        |--------------------------------------------------------------------------
        */

        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->subDays(29)->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();


        /*
        |--------------------------------------------------------------------------
        | PREVIOUS PERIOD
        |--------------------------------------------------------------------------
        */

        $daysInPeriod = $startDate->copy()->startOfDay()
            ->diffInDays($endDate->copy()->startOfDay()) + 1;

        $previousEndDate = $startDate->copy()
            ->subDay()
            ->endOfDay();

        $previousStartDate = $previousEndDate->copy()
            ->subDays($daysInPeriod - 1)
            ->startOfDay();


        $percentageChange = function ($current, $previous) {

            $current = (float) $current;
            $previous = (float) $previous;

            if ($previous == 0) {
                return null;
            }

            return round(
                (($current - $previous) / abs($previous)) * 100,
                1
            );
        };

        /*
        |--------------------------------------------------------------------------
        | SALE ITEMS
        |--------------------------------------------------------------------------
        |
        | - Uses SALE invoice_date
        | - Includes product + other
        | - Excludes cancelled sales
        | - Product status does NOT matter
        |
        */

        $saleItems = SaleItem::query()
            ->whereIn('type', ['product', 'other'])
            ->whereHas('sale', function ($query) use ($startDate, $endDate) {

                $query
                    ->whereDate(
                        'invoice_date',
                        '>=',
                        $startDate->toDateString()
                    )
                    ->whereDate(
                        'invoice_date',
                        '<=',
                        $endDate->toDateString()
                    )
                    ->where(function ($query) {

                        $query
                            ->whereNull('status')
                            ->orWhere('status', '!=', 'cancelled');

                    });

            })
            ->with([
                'sale',
                'product.prices',
                'product.partAllocations',
            ])
            ->get();


        /*
        |--------------------------------------------------------------------------
        | PREVIOUS PERIOD SALE ITEMS
        |--------------------------------------------------------------------------
        */

        $previousSaleItems = SaleItem::query()
            ->whereIn('type', ['product', 'other'])
            ->whereHas('sale', function ($query) use (
                $previousStartDate,
                $previousEndDate
            ) {

                $query
                    ->whereDate(
                        'invoice_date',
                        '>=',
                        $previousStartDate->toDateString()
                    )
                    ->whereDate(
                        'invoice_date',
                        '<=',
                        $previousEndDate->toDateString()
                    )
                    ->where(function ($query) {

                        $query
                            ->whereNull('status')
                            ->orWhere('status', '!=', 'cancelled');

                    });

            })
            ->with([
                'sale',
                'product.prices',
                'product.partAllocations',
            ])
            ->get();

            /*
            |--------------------------------------------------------------------------
            | PREVIOUS SALES PERFORMANCE
            |--------------------------------------------------------------------------
            */

            $previousSalesRevenue = $previousSaleItems->sum(function ($item) {
                return (float) $item->total;
            });


            $previousPurchaseCost = $previousSaleItems->sum(function ($item) {

                if (!$item->product) {
                    return 0;
                }

                return (float) optional(
                    $item->product->prices->firstWhere('type', 'purchase')
                )->price;

            });


            $previousPartsCost = $previousSaleItems->sum(function ($item) {

                if (!$item->product) {
                    return 0;
                }

                return (float) $item
                    ->product
                    ->partAllocations
                    ->sum('cost_allocated');

            });


            $previousTotalCost =
                $previousPurchaseCost +
                $previousPartsCost;


            $previousGrossProfit =
                $previousSalesRevenue -
                $previousTotalCost;


            $previousProductsSold = $previousSaleItems
                ->where('type', 'product')
                ->sum('qty');


            $previousMargin = $previousSalesRevenue > 0
                ? ($previousGrossProfit / $previousSalesRevenue) * 100
                : 0;


        /*
        |--------------------------------------------------------------------------
        | SALES REVENUE
        |--------------------------------------------------------------------------
        |
        | Includes:
        | product
        | other
        |
        | Does NOT include delivery.
        |
        */

        $salesRevenue = $saleItems->sum(function ($item) {
            return (float) $item->total;
        });


        /*
        |--------------------------------------------------------------------------
        | PURCHASE COST OF PRODUCTS SOLD
        |--------------------------------------------------------------------------
        |
        | Only actual products have a product relationship.
        | "Other" items contribute £0 cost here.
        |
        */

        $purchaseCost = $saleItems->sum(function ($item) {

            if (!$item->product) {
                return 0;
            }

            return (float) optional(
                $item->product->prices->firstWhere('type', 'purchase')
            )->price;

        });


        /*
        |--------------------------------------------------------------------------
        | PARTS / REFURB COST
        |--------------------------------------------------------------------------
        */

        $partsCost = $saleItems->sum(function ($item) {

            if (!$item->product) {
                return 0;
            }

            return (float) $item
                ->product
                ->partAllocations
                ->sum('cost_allocated');

        });


        /*
        |--------------------------------------------------------------------------
        | TOTAL PRODUCT COST
        |--------------------------------------------------------------------------
        */

        $totalCost = $purchaseCost + $partsCost;


        /*
        |--------------------------------------------------------------------------
        | GROSS PROFIT
        |--------------------------------------------------------------------------
        */

        $grossProfit = $salesRevenue - $totalCost;


        /*
        |--------------------------------------------------------------------------
        | GROSS MARGIN
        |--------------------------------------------------------------------------
        */

        $margin = $salesRevenue > 0
            ? round(
                ($grossProfit / $salesRevenue) * 100,
                1
            )
            : 0;


        /*
        |--------------------------------------------------------------------------
        | NUMBER OF PRODUCTS SOLD
        |--------------------------------------------------------------------------
        |
        | Do NOT count "other" items as products.
        |
        */

        $productsSold = $saleItems
            ->where('type', 'product')
            ->sum('qty');


        /*
        |--------------------------------------------------------------------------
        | AVERAGE SALE VALUE
        |--------------------------------------------------------------------------
        */

        $averageSale = $productsSold > 0
            ? $salesRevenue / $productsSold
            : 0;


        /*
        |--------------------------------------------------------------------------
        | AVERAGE PROFIT PER PRODUCT
        |--------------------------------------------------------------------------
        */

        $averageProfit = $productsSold > 0
            ? $grossProfit / $productsSold
            : 0;


        /*
        |--------------------------------------------------------------------------
        | BUSINESS ACTIVITY
        |--------------------------------------------------------------------------
        |
        | What actually happened financially during this date range.
        |
        | MONEY IN:
        | - Product sales
        | - Other sale items
        |
        | MONEY OUT:
        | - Stock purchases
        | - Parts bought
        | - Fuel
        |
        | This is NOT profit.
        |
        */


        /*
        |--------------------------------------------------------------------------
        | MONEY IN - SALES
        |--------------------------------------------------------------------------
        */

        $activitySales = Sale::query()

        ->whereDate(
            'invoice_date',
            '>=',
            $startDate->toDateString()
        )

        ->whereDate(
            'invoice_date',
            '<=',
            $endDate->toDateString()
        )

        ->where(function ($query) {

            $query
                ->whereNull('status')
                ->orWhere('status', '!=', 'cancelled');

        })

        ->sum('total_amount');


        /*
        |--------------------------------------------------------------------------
        | MONEY OUT - STOCK PURCHASES
        |--------------------------------------------------------------------------
        */

        $activityPurchases = Purchase::query()
            ->whereDate(
                'purchase_date',
                '>=',
                $startDate->toDateString()
            )
            ->whereDate(
                'purchase_date',
                '<=',
                $endDate->toDateString()
            )
            ->sum('total_amount');


       /*
        |--------------------------------------------------------------------------
        | MONEY OUT - PARTS
        |--------------------------------------------------------------------------
        */

        $activityParts = Part::query()
            ->whereDate(
                'purchased_at',
                '>=',
                $startDate->toDateString()
            )
            ->whereDate(
                'purchased_at',
                '<=',
                $endDate->toDateString()
            )
            ->sum('total_cost');


        /*
        |--------------------------------------------------------------------------
        | MONEY OUT - FUEL
        |--------------------------------------------------------------------------
        */

        $activityFuel = FuelLog::query()
            ->whereDate(
                'date',
                '>=',
                $startDate->toDateString()
            )
            ->whereDate(
                'date',
                '<=',
                $endDate->toDateString()
            )
            ->sum('cost');


        /*
        |--------------------------------------------------------------------------
        | ACTIVITY TOTALS
        |--------------------------------------------------------------------------
        */

        $moneyIn = (float) $activitySales;

        $moneyOut =
            (float) $activityPurchases
            + (float) $activityParts
            + (float) $activityFuel;

        $netActivity = $moneyIn - $moneyOut;



        /*
|--------------------------------------------------------------------------
| ACTIVITY FEED
|--------------------------------------------------------------------------
*/

$activityFeed = collect();


/*
|--------------------------------------------------------------------------
| SALES
|--------------------------------------------------------------------------
*/

$salesFeed = SaleItem::query()
    ->whereIn('type', ['product', 'other'])
    ->whereHas('sale', function ($query) use ($startDate, $endDate) {
        $query
            ->whereDate('invoice_date', '>=', $startDate->toDateString())
            ->whereDate('invoice_date', '<=', $endDate->toDateString())
            ->where(function ($query) {
                $query
                    ->whereNull('status')
                    ->orWhere('status', '!=', 'cancelled');
            });
    })
    ->with([
        'sale.contact',
        'product',
    ])
    ->get()
    ->map(function ($item) {

        return [
            'id' => 'sale-item-' . $item->id,

            'type' => 'sale',

            'date' => $item->sale->invoice_date,

            'reference' => $item->sale->reference,

            'title' => $item->product?->title
                ?? $item->description
                ?? 'Sale',

            'subtitle' => $item->sale->contact?->name,

            'amount' => (float) $item->total,

            'direction' => 'in',

            'record_id' => $item->sale->id,
        ];

    });

$activityFeed = $activityFeed->concat($salesFeed);


/*
|--------------------------------------------------------------------------
| PURCHASES
|--------------------------------------------------------------------------
*/

$purchasesFeed = Purchase::query()
    ->whereDate('purchase_date', '>=', $startDate->toDateString())
    ->whereDate('purchase_date', '<=', $endDate->toDateString())
    ->with([
        'contact',
        'products',
    ])
    ->get()
    ->map(function ($purchase) {

        return [
            'id' => 'purchase-' . $purchase->id,

            'type' => 'purchase',

            'date' => $purchase->purchase_date,

            'reference' => $purchase->reference
                ?? 'P-' . $purchase->id,

            'title' => $purchase->contact?->name
                ?? 'Stock Purchase',

            'subtitle' => $purchase->products->count()
                . ' product'
                . ($purchase->products->count() === 1 ? '' : 's'),

            'amount' => (float) $purchase->total_amount,

            'direction' => 'out',

            'record_id' => $purchase->id,
        ];

    });

$activityFeed = $activityFeed->concat($purchasesFeed);


/*
|--------------------------------------------------------------------------
| PARTS
|--------------------------------------------------------------------------
*/

$partsFeed = Part::query()
    ->whereDate('purchased_at', '>=', $startDate->toDateString())
    ->whereDate('purchased_at', '<=', $endDate->toDateString())
    ->get()
    ->map(function ($part) {

        return [
            'id' => 'part-' . $part->id,

            'type' => 'part',

            'date' => $part->purchased_at,

            'reference' => null,

            'title' => $part->name ?? 'Part',

            'subtitle' => $part->quantity
                ? $part->quantity . ' purchased'
                : null,

            'amount' => (float) $part->total_cost,

            'direction' => 'out',

            'record_id' => $part->id,
        ];

    });

$activityFeed = $activityFeed->concat($partsFeed);


/*
|--------------------------------------------------------------------------
| FUEL
|--------------------------------------------------------------------------
*/

$fuelFeed = FuelLog::query()
    ->whereDate('date', '>=', $startDate->toDateString())
    ->whereDate('date', '<=', $endDate->toDateString())
    ->with('vehicle')
    ->get()
    ->map(function ($fuel) {

        return [
            'id' => 'fuel-' . $fuel->id,

            'type' => 'fuel',

            'date' => $fuel->date,

            'reference' => null,

            'title' => 'Fuel',

            'subtitle' => $fuel->vehicle?->name,

            'amount' => (float) $fuel->cost,

            'direction' => 'out',

            'record_id' => $fuel->id,
        ];

    });

$activityFeed = $activityFeed->concat($fuelFeed);


/*
|--------------------------------------------------------------------------
| SORT NEWEST FIRST
|--------------------------------------------------------------------------
*/

$activityFeed = $activityFeed
    ->sortByDesc('date')
    ->values();

    $salesRevenueChange = $percentageChange(
    $salesRevenue,
    $previousSalesRevenue
);

$grossProfitChange = $percentageChange(
    $grossProfit,
    $previousGrossProfit
);

$productsSoldChange = $percentageChange(
    $productsSold,
    $previousProductsSold
);

$marginChange = round(
    $margin - $previousMargin,
    1
);

$previousActivityPurchases = Purchase::query()
    ->whereDate(
        'purchase_date',
        '>=',
        $previousStartDate->toDateString()
    )
    ->whereDate(
        'purchase_date',
        '<=',
        $previousEndDate->toDateString()
    )
    ->sum('total_amount');


$previousActivityParts = Part::query()
    ->whereDate(
        'purchased_at',
        '>=',
        $previousStartDate->toDateString()
    )
    ->whereDate(
        'purchased_at',
        '<=',
        $previousEndDate->toDateString()
    )
    ->sum('total_cost');


$previousActivityFuel = FuelLog::query()
    ->whereDate(
        'date',
        '>=',
        $previousStartDate->toDateString()
    )
    ->whereDate(
        'date',
        '<=',
        $previousEndDate->toDateString()
    )
    ->sum('cost');


    $previousMoneyIn = (float) $previousSalesRevenue;

$previousMoneyOut =
    (float) $previousActivityPurchases
    + (float) $previousActivityParts
    + (float) $previousActivityFuel;

$previousNetActivity =
    $previousMoneyIn -
    $previousMoneyOut;


    $moneyInChange = $percentageChange(
    $moneyIn,
    $previousMoneyIn
);

$moneyOutChange = $percentageChange(
    $moneyOut,
    $previousMoneyOut
);

$netActivityChange = $percentageChange(
    $netActivity,
    $previousNetActivity
);



/*
|--------------------------------------------------------------------------
| DAILY BUSINESS TREND
|--------------------------------------------------------------------------
|
| Creates one record for every day in the selected period.
|
| money_in:
|   product + other sale items
|
| money_out:
|   purchases + parts + fuel
|
| gross_profit:
|   sale revenue - original stock cost - allocated parts cost
|
*/

$dailyTrend = collect();

$currentDate = $startDate->copy()->startOfDay();

while ($currentDate->lte($endDate)) {

    $date = $currentDate->toDateString();


    /*
    |--------------------------------------------------------------------------
    | SALES FOR THIS DAY
    |--------------------------------------------------------------------------
    */

    $daySaleItems = $saleItems->filter(function ($item) use ($date) {

        if (!$item->sale || !$item->sale->invoice_date) {
            return false;
        }

        return Carbon::parse($item->sale->invoice_date)
            ->toDateString() === $date;

    });


    /*
    |--------------------------------------------------------------------------
    | MONEY IN
    |--------------------------------------------------------------------------
    */

    $dayMoneyIn = $daySaleItems->sum(function ($item) {
        return (float) $item->total;
    });


    /*
    |--------------------------------------------------------------------------
    | PRODUCTS SOLD
    |--------------------------------------------------------------------------
    */

    $dayProductsSold = $daySaleItems
        ->where('type', 'product')
        ->sum('qty');


    /*
    |--------------------------------------------------------------------------
    | COST OF PRODUCTS SOLD
    |--------------------------------------------------------------------------
    */

    $dayProductCost = $daySaleItems->sum(function ($item) {

        if (!$item->product) {
            return 0;
        }

        return (float) optional(
            $item->product->prices->firstWhere('type', 'purchase')
        )->price;

    });


    /*
    |--------------------------------------------------------------------------
    | REFURB COST OF PRODUCTS SOLD
    |--------------------------------------------------------------------------
    */

    $dayRefurbCost = $daySaleItems->sum(function ($item) {

        if (!$item->product) {
            return 0;
        }

        return (float) $item
            ->product
            ->partAllocations
            ->sum('cost_allocated');

    });


    /*
    |--------------------------------------------------------------------------
    | GROSS PROFIT
    |--------------------------------------------------------------------------
    */

    $dayGrossProfit =
        $dayMoneyIn
        - $dayProductCost
        - $dayRefurbCost;


    /*
    |--------------------------------------------------------------------------
    | PURCHASES MADE THIS DAY
    |--------------------------------------------------------------------------
    */

    $dayPurchases = Purchase::query()
        ->whereDate('purchase_date', $date)
        ->sum('total_amount');


    /*
    |--------------------------------------------------------------------------
    | PARTS BOUGHT THIS DAY
    |--------------------------------------------------------------------------
    |
    | Confirmed fields:
    | purchased_at
    | total_cost
    |
    */

    $dayParts = Part::query()
        ->whereDate('purchased_at', $date)
        ->sum('total_cost');


    /*
    |--------------------------------------------------------------------------
    | FUEL BOUGHT THIS DAY
    |--------------------------------------------------------------------------
    */

    $dayFuel = FuelLog::query()
        ->whereDate('date', $date)
        ->sum('cost');


    /*
    |--------------------------------------------------------------------------
    | MONEY OUT
    |--------------------------------------------------------------------------
    */

    $dayMoneyOut =
        (float) $dayPurchases
        + (float) $dayParts
        + (float) $dayFuel;


    /*
    |--------------------------------------------------------------------------
    | NET ACTIVITY
    |--------------------------------------------------------------------------
    |
    | Remember: this is NOT profit.
    |
    */

    $dayNetActivity =
        $dayMoneyIn
        - $dayMoneyOut;


    /*
    |--------------------------------------------------------------------------
    | ADD DAY
    |--------------------------------------------------------------------------
    */

    $dailyTrend->push([

        'date' => $date,

        'money_in' => round($dayMoneyIn, 2),

        'money_out' => round($dayMoneyOut, 2),

        'net_activity' => round($dayNetActivity, 2),

        'purchases' => round((float) $dayPurchases, 2),

        'parts' => round((float) $dayParts, 2),

        'fuel' => round((float) $dayFuel, 2),

        'product_cost' => round($dayProductCost, 2),

        'refurb_cost' => round($dayRefurbCost, 2),

        'gross_profit' => round($dayGrossProfit, 2),

        'products_sold' => (int) $dayProductsSold,

    ]);


    $currentDate->addDay();
}

/*
|--------------------------------------------------------------------------
| TREND GROUPING
|--------------------------------------------------------------------------
|
| Short periods  = daily
| Medium periods = weekly
| Long periods   = monthly
|
*/

$trendDays = $startDate->copy()
    ->startOfDay()
    ->diffInDays($endDate->copy()->startOfDay()) + 1;


if ($trendDays <= 45) {

    $trendGrouping = 'daily';

    $trend = $dailyTrend->map(function ($item) {

        return [
            ...$item,

            'label' => Carbon::parse($item['date'])
                ->format('j M'),
        ];

    });

} elseif ($trendDays <= 180) {

    /*
    |--------------------------------------------------------------------------
    | WEEKLY
    |--------------------------------------------------------------------------
    */

    $trendGrouping = 'weekly';

    $trend = $dailyTrend
        ->groupBy(function ($item) {

            return Carbon::parse($item['date'])
                ->startOfWeek()
                ->toDateString();

        })
        ->map(function ($items, $weekStart) {

            $start = Carbon::parse($weekStart);

            $end = $start->copy()->endOfWeek();

            return [

                'date' => $weekStart,

                'label' =>
                    $start->format('j M')
                    . ' - '
                    . $end->format('j M'),

                'money_in' => round(
                    $items->sum('money_in'),
                    2
                ),

                'money_out' => round(
                    $items->sum('money_out'),
                    2
                ),

                'net_activity' => round(
                    $items->sum('net_activity'),
                    2
                ),

                'purchases' => round(
                    $items->sum('purchases'),
                    2
                ),

                'parts' => round(
                    $items->sum('parts'),
                    2
                ),

                'fuel' => round(
                    $items->sum('fuel'),
                    2
                ),

                'product_cost' => round(
                    $items->sum('product_cost'),
                    2
                ),

                'refurb_cost' => round(
                    $items->sum('refurb_cost'),
                    2
                ),

                'gross_profit' => round(
                    $items->sum('gross_profit'),
                    2
                ),

                'products_sold' => (int)
                    $items->sum('products_sold'),

            ];

        })
        ->values();

} else {

    /*
    |--------------------------------------------------------------------------
    | MONTHLY
    |--------------------------------------------------------------------------
    */

    $trendGrouping = 'monthly';

    $trend = $dailyTrend
        ->groupBy(function ($item) {

            return Carbon::parse($item['date'])
                ->startOfMonth()
                ->toDateString();

        })
        ->map(function ($items, $monthStart) {

            $date = Carbon::parse($monthStart);

            return [

                'date' => $monthStart,

                'label' => $date->format('M Y'),

                'money_in' => round(
                    $items->sum('money_in'),
                    2
                ),

                'money_out' => round(
                    $items->sum('money_out'),
                    2
                ),

                'net_activity' => round(
                    $items->sum('net_activity'),
                    2
                ),

                'purchases' => round(
                    $items->sum('purchases'),
                    2
                ),

                'parts' => round(
                    $items->sum('parts'),
                    2
                ),

                'fuel' => round(
                    $items->sum('fuel'),
                    2
                ),

                'product_cost' => round(
                    $items->sum('product_cost'),
                    2
                ),

                'refurb_cost' => round(
                    $items->sum('refurb_cost'),
                    2
                ),

                'gross_profit' => round(
                    $items->sum('gross_profit'),
                    2
                ),

                'products_sold' => (int)
                    $items->sum('products_sold'),

            ];

        })
        ->values();
}

/*
|--------------------------------------------------------------------------
| STOCK HEALTH
|--------------------------------------------------------------------------
|
| For now:
|
| Stock age = Purchase purchase_date -> today
|
| Current stock:
| - pending
| - listed
|
| Excluded:
| - sold
|
*/

$currentStock = Product::query()
    ->whereIn('status', ['pending', 'listed'])
    ->with([
        'purchase',
        'prices',
        'partAllocations',
        'primaryImage',
    ])
    ->get();


/*
|--------------------------------------------------------------------------
| STOCK COUNTS
|--------------------------------------------------------------------------
*/

$totalStock = $currentStock->count();

$pendingStock = $currentStock
    ->where('status', 'pending')
    ->count();

$listedStock = $currentStock
    ->where('status', 'listed')
    ->count();


/*
|--------------------------------------------------------------------------
| PURCHASE VALUE
|--------------------------------------------------------------------------
*/

$stockPurchaseValue = $currentStock->sum(function ($product) {

    return (float) optional(
        $product->prices->firstWhere('type', 'purchase')
    )->price;

});


/*
|--------------------------------------------------------------------------
| REFURB / PARTS INVESTED
|--------------------------------------------------------------------------
*/

$stockPartsValue = $currentStock->sum(function ($product) {

    return (float) $product
        ->partAllocations
        ->sum('cost_allocated');

});


/*
|--------------------------------------------------------------------------
| TOTAL CASH INVESTED IN CURRENT STOCK
|--------------------------------------------------------------------------
*/

$stockInvestedValue =
    $stockPurchaseValue +
    $stockPartsValue;


/*
|--------------------------------------------------------------------------
| WEBSITE / ASKING VALUE
|--------------------------------------------------------------------------
*/

$stockRetailValue = $currentStock->sum(function ($product) {

    return (float) optional(
        $product->prices->firstWhere('type', 'website')
    )->price;

});


/*
|--------------------------------------------------------------------------
| POTENTIAL GROSS PROFIT
|--------------------------------------------------------------------------
|
| This is NOT realised profit.
|
| It simply shows:
|
| website asking value
| - purchase cost
| - refurb cost
|
*/

$potentialGrossProfit =
    $stockRetailValue -
    $stockInvestedValue;


/*
|--------------------------------------------------------------------------
| AVERAGES
|--------------------------------------------------------------------------
*/

$averageStockCost = $totalStock > 0
    ? $stockInvestedValue / $totalStock
    : 0;

$averageRetailValue = $totalStock > 0
    ? $stockRetailValue / $totalStock
    : 0;

    /*
|--------------------------------------------------------------------------
| STOCK AGE
|--------------------------------------------------------------------------
*/

$stockAge = [
    '0_30' => 0,
    '31_60' => 0,
    '61_90' => 0,
    '91_180' => 0,
    '180_plus' => 0,
];


foreach ($currentStock as $product) {

    if (
        !$product->purchase ||
        !$product->purchase->purchase_date
    ) {
        continue;
    }

   $age = (int) Carbon::parse(
        $product->purchase->purchase_date
    )
        ->startOfDay()
        ->diffInDays(now()->startOfDay());


    if ($age <= 30) {

        $stockAge['0_30']++;

    } elseif ($age <= 60) {

        $stockAge['31_60']++;

    } elseif ($age <= 90) {

        $stockAge['61_90']++;

    } elseif ($age <= 180) {

        $stockAge['91_180']++;

    } else {

        $stockAge['180_plus']++;

    }

}

/*
|--------------------------------------------------------------------------
| OLDEST STOCK
|--------------------------------------------------------------------------
*/

$oldestStock = $currentStock
    ->filter(function ($product) {

        return $product->purchase
            && $product->purchase->purchase_date;

    })
    ->map(function ($product) {

        $purchaseDate = Carbon::parse(
            $product->purchase->purchase_date
        );

        $purchasePrice = (float) optional(
            $product->prices->firstWhere('type', 'purchase')
        )->price;

        $partsCost = (float) $product
            ->partAllocations
            ->sum('cost_allocated');

        $websitePrice = (float) optional(
            $product->prices->firstWhere('type', 'website')
        )->price;


        return [

            'id' => $product->id,

            'sku' => $product->sku,

            'title' => $product->title,

            'status' => $product->status,

           'primary_image' => $product->primaryImage,

            'purchase_date' =>
                $purchaseDate->toDateString(),

            'age_days' => (int) $purchaseDate
                ->startOfDay()
                ->diffInDays(now()->startOfDay()),

            'purchase_cost' =>
                round($purchasePrice, 2),

            'parts_cost' =>
                round($partsCost, 2),

            'total_cost' =>
                round(
                    $purchasePrice + $partsCost,
                    2
                ),

            'website_price' =>
                round($websitePrice, 2),

        ];

    })
    ->sortByDesc('age_days')
    ->take(10)
    ->values();

    // dd($oldestStock);

    
    

        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Analytics/Index', [

            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],

            'summary' => [

                'sales_revenue' => round($salesRevenue, 2),

                'purchase_cost' => round($purchaseCost, 2),

                'parts_cost' => round($partsCost, 2),

                'total_cost' => round($totalCost, 2),

                'gross_profit' => round($grossProfit, 2),

                'margin' => $margin,

                'products_sold' => $productsSold,

                'average_sale' => round($averageSale, 2),

                'average_profit' => round($averageProfit, 2),

            ],

            'activitySummary' => [
                'money_in' => round($moneyIn, 2),
                'money_out' => round($moneyOut, 2),
                'net_activity' => round($netActivity, 2),

                'sales' => round((float) $activitySales, 2),
                'purchases' => round((float) $activityPurchases, 2),
                'parts' => round((float) $activityParts, 2),
                'fuel' => round((float) $activityFuel, 2),
            ],

            'activityFeed' => $activityFeed,

            'comparison' => [

                'previous_period' => [
                    'start_date' => $previousStartDate->toDateString(),
                    'end_date' => $previousEndDate->toDateString(),
                ],

                'sales' => [
                    'revenue' => $salesRevenueChange,
                    'gross_profit' => $grossProfitChange,
                    'products_sold' => $productsSoldChange,
                    'margin_points' => $marginChange,
                ],

                'activity' => [
                    'money_in' => $moneyInChange,
                    'money_out' => $moneyOutChange,
                    'net_activity' => $netActivityChange,
                ],

            ],

            'dailyTrend' => $dailyTrend,
            'trend' => $trend,
            'trendGrouping' => $trendGrouping,

            'stockHealth' => [

                'total_stock' => $totalStock,

                'pending' => $pendingStock,

                'listed' => $listedStock,

                'purchase_value' => round(
                    $stockPurchaseValue,
                    2
                ),

                'parts_value' => round(
                    $stockPartsValue,
                    2
                ),

                'invested_value' => round(
                    $stockInvestedValue,
                    2
                ),

                'retail_value' => round(
                    $stockRetailValue,
                    2
                ),

                'potential_gross_profit' => round(
                    $potentialGrossProfit,
                    2
                ),

                'average_cost' => round(
                    $averageStockCost,
                    2
                ),

                'average_retail' => round(
                    $averageRetailValue,
                    2
                ),

                'age' => $stockAge,

                'oldest' => $oldestStock,

            ],

        ]);
    }
}