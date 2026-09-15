<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Part;
use App\Models\FuelLog;
use App\Models\SaleItem;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {

        // ---------------------
        // FUEL STATS
        // ---------------------

        $thisWeekStart = Carbon::now()->startOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        $totalFuel = FuelLog::sum('cost');

        $thisWeekFuel = FuelLog::whereBetween('date', [$thisWeekStart, now()])
            ->sum('cost');

        $lastWeekFuel = FuelLog::whereBetween('date', [$lastWeekStart, $lastWeekEnd])
            ->sum('cost');

        $thisMonthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $thisMonthFuel = FuelLog::whereBetween('date', [$thisMonthStart, now()])
            ->sum('cost');

        $lastMonthFuel = FuelLog::whereBetween('date', [$lastMonthStart, $lastMonthEnd])
            ->sum('cost');

        $now = now();

        $today = Purchase::whereDate('purchase_date', $now->toDateString())->sum('total_amount');

        $last7 = Purchase::whereBetween('purchase_date', [
            $now->copy()->subDays(7), $now
        ])->sum('total_amount');

        $last31 = Purchase::whereBetween('purchase_date', [
            $now->copy()->subDays(31), $now
        ])->sum('total_amount');

        $prev31 = Purchase::whereBetween('purchase_date', [
            $now->copy()->subDays(62), $now->copy()->subDays(31)
        ])->sum('total_amount');

        $yearToDate = Purchase::whereBetween('created_at', [
            $now->copy()->startOfYear(),
            $now
        ])->sum('total_amount');

        $percentageChange = $prev31 > 0
            ? round((($last31 - $prev31) / $prev31) * 100, 1)
            : null;

        $lastYear = Purchase::whereBetween('created_at', [
            $now->copy()->subYear()->startOfYear(),
            $now->copy()->subYear()->endOfYear(),
        ])->sum('total_amount');

        $grandTotal = Purchase::sum('total_amount');

        $avgDaily = round($last31 / 31, 2);

        $thisWeekDelivery = SaleItem::query()
            ->where('type', 'delivery')
            ->whereHas('sale', function ($q) {
                $q->whereBetween('invoice_date', [
                    now()->startOfWeek(),
                    now()
                ]);
            })
            ->sum('total');

        $totalDeliveryRevenue = SaleItem::where('type', 'delivery')
            ->sum('total');

        $thisMonthDelivery = SaleItem::query()
            ->where('type', 'delivery')
            ->whereHas('sale', function ($q) {
                $q->whereBetween('invoice_date', [
                    now()->startOfMonth(),
                    now()
                ]);
            })
            ->sum('total');

        $weeklyProfit = $thisWeekDelivery - $thisWeekFuel;
        $monthlyProfit = $thisMonthDelivery - $thisMonthFuel;

        $weeklyCoverage = $thisWeekFuel > 0
            ? ($thisWeekDelivery / $thisWeekFuel) * 100
            : 0;

        $monthlyCoverage = $thisMonthFuel > 0
                ? ($thisMonthDelivery / $thisMonthFuel) * 100
                : 0;

        $saleLast31Revenue = Sale::where('invoice_date', '>=', now()->subDays(31))
            ->sum('total_amount');

        $saleLast31Products = SaleItem::whereHas('sale', function ($q) {
                $q->where('invoice_date', '>=', now()->subDays(31));
            })
            ->where('type', 'product')
            ->sum('qty');

        $salePrev31Revenue = Sale::whereBetween('invoice_date', [
                now()->copy()->subDays(62),
                now()->copy()->subDays(31)
            ])
            ->sum('total_amount');

        $salePercentageChange = $salePrev31Revenue > 0
            ? round((($saleLast31Revenue - $salePrev31Revenue) / $salePrev31Revenue) * 100, 1)
            : null;

        $salesStats = [

            'today' => [
                'revenue' => Sale::whereDate('invoice_date', today())
                    ->sum('total_amount'),

                'products' => SaleItem::whereHas('sale', function ($q) {
                        $q->whereDate('invoice_date', today());
                    })
                    ->where('type', 'product')
                    ->sum('qty'),
            ],

            'total' => [
                'revenue' => Sale::sum('total_amount'),

                'products' => SaleItem::where('type', 'product')
                    ->sum('qty'),
            ],

            'last_90_days' => [
                'revenue' => Sale::where('invoice_date', '>=', now()->subDays(90))
                    ->sum('total_amount'),

                'products' => SaleItem::whereHas('sale', function ($q) {
                        $q->where('invoice_date', '>=', now()->subDays(90));
                    })
                    ->where('type', 'product')
                    ->sum('qty'),
            ],

            'last_31_days' => [
                'revenue' => $saleLast31Revenue,

                'products' => $saleLast31Products,
            ],

            'last_7_days' => [
                'revenue' => Sale::where('invoice_date', '>=', now()->subDays(7))
                    ->sum('total_amount'),

                'products' => SaleItem::whereHas('sale', function ($q) {
                        $q->where('invoice_date', '>=', now()->subDays(7));
                    })
                    ->where('type', 'product')
                    ->sum('qty'),
            ],

            'percentageChange' => $salePercentageChange,
        ];

        $startDate = request('start_date')
            ? Carbon::parse(request('start_date'))->startOfDay()
            : now()->subDays(31)->startOfDay();

        $endDate = request('end_date')
            ? Carbon::parse(request('end_date'))->endOfDay()
            : now()->endOfDay();

        // gross calculations 

        $saleItems = SaleItem::query()
            ->where('type', 'product')
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('invoice_date', [$startDate, $endDate])
                    ->where('status', '!=', 'cancelled');
            })
            ->with([
                'sale',
                'product.prices',
                'product.partAllocations',
            ])
        ->get();

        $salesRevenue = $saleItems->sum('total');

        $purchaseCost = $saleItems->sum(function ($item) {
            return (float) optional($item->product?->prices?->firstWhere('type', 'purchase'))->price;
        });

        $partsCost = $saleItems->sum(function ($item) {
            return $item->product?->partAllocations?->sum('cost_allocated') ?? 0;
        });

        $totalCost = $purchaseCost + $partsCost;

        $grossProfit = $salesRevenue - $totalCost;

        $margin = $salesRevenue > 0
            ? round(($grossProfit / $salesRevenue) * 100, 1)
            : 0;


        $fuelLogsByVehicle = FuelLog::query()
            ->with('vehicle')
            ->whereNotNull('vehicle_id')
            ->whereNotNull('mileage')
            ->orderBy('vehicle_id')
            ->orderBy('date')
            ->get()
            ->groupBy('vehicle_id');

        $vehicleMileageStats = [];

        foreach ($fuelLogsByVehicle as $vehicleId => $logs) {

            $weeklyMileage = [];
            $previousLog = null;

            foreach ($logs as $log) {

                // First reading is only our starting point
                if ($previousLog === null) {
                    $previousLog = $log;
                    continue;
                }

                $miles = (float) $log->mileage - (float) $previousLog->mileage;

                if ($miles >= 0 && $miles <= 5000) {

                    $weekKey = Carbon::parse($log->date)
                        ->startOfWeek()
                        ->format('Y-m-d');

                    $weeklyMileage[$weekKey] =
                        ($weeklyMileage[$weekKey] ?? 0) + $miles;
                }

                $previousLog = $log;
            }

            $thisWeekKey = now()
                ->copy()
                ->startOfWeek()
                ->format('Y-m-d');

            $lastWeekKey = now()
                ->copy()
                ->subWeek()
                ->startOfWeek()
                ->format('Y-m-d');

            $last12Weeks = collect($weeklyMileage)
                ->sortKeys()
                ->take(-12);

            $vehicle = $logs->first()->vehicle;

            $vehicleMileageStats[] = [
                'vehicle_id' => $vehicleId,

                'vehicle' => $vehicle?->name ?? 'Vehicle',

                'registration' => $vehicle?->registration ?? null,

                'this_week' => round($weeklyMileage[$thisWeekKey] ?? 0),

                'last_week' => round($weeklyMileage[$lastWeekKey] ?? 0),

                'average_week' => $last12Weeks->count()
                    ? round($last12Weeks->avg())
                    : 0,

                'last_12_weeks' => round($last12Weeks->sum()),

                'weekly' => $last12Weeks
                    ->map(fn ($miles, $week) => [
                        'week' => $week,
                        'miles' => round($miles),
                    ])
                    ->values(),
            ];
        }

        $businessPerformance = $this->getBusinessPerformance(
                now()->subDays(31),
                now()
            );
                


        return inertia('Dashboard', [
            'purchaseStats' => [
                'today' => $today,
                'last7' => $last7,
                'last31' => $last31,
                'yearToDate' => $yearToDate,
                'lastYear' => $lastYear,
                'grandTotal' => $grandTotal,
                'last90' => Purchase::whereBetween('purchase_date', [
                    $now->copy()->subDays(90), $now
                ])->sum('total_amount'),
                'percentageChange' => $percentageChange,
                'avgDaily' => $avgDaily,
            ],
            'fuel' => [
                'this_week' => $thisWeekFuel,
                'last_week' => $lastWeekFuel,
                'this_month' => $thisMonthFuel,
                'last_month' => $lastMonthFuel,
                'total' => $totalFuel,
            ],
            'delivery' => [
                'this_week_revenue' => $thisWeekDelivery,
                'this_month_revenue' => $thisMonthDelivery,
                'total_revenue' => $totalDeliveryRevenue,

                'weekly_profit' => $weeklyProfit,
                'monthly_profit' => $monthlyProfit,

                'weekly_coverage' => round($weeklyCoverage, 1),
                'monthly_coverage' => round($monthlyCoverage, 1),
            ],
            'salesStats' => $salesStats,
            'profitStats' => [
                'sales_revenue' => $salesRevenue,
                'purchase_cost' => $purchaseCost,
                'parts_cost' => $partsCost,
                'total_cost' => $totalCost,
                'gross_profit' => $grossProfit,
                'margin' => $margin,
                'product_count' => $saleItems->sum('qty'),
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],

            'vehicleMileageStats' => $vehicleMileageStats,

            'businessPerformance' => $businessPerformance,
            
        ]);
    }

    public function businessPerformance(Request $request)
    {
        $request->validate([
            'period' => 'nullable|in:today,7,31,90,custom',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $period = $request->input('period', '31');

        switch ($period) {
            case 'today':
                $from = now()->startOfDay();
                $to = now()->endOfDay();
                break;

            case '7':
                $from = now()->subDays(7);
                $to = now();
                break;

            case '90':
                $from = now()->subDays(90);
                $to = now();
                break;

            case 'custom':
                $from = $request->from;
                $to = $request->to;
                break;

            case '31':
            default:
                $from = now()->subDays(31);
                $to = now();
                break;
        }

        return response()->json(
            $this->getBusinessPerformance($from, $to)
        );
    }

    private function getBusinessPerformance($from, $to)
    {
        // Make sure we include the whole start/end day
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        // -------------------------
        // INCOME
        // -------------------------

        $sales = Sale::whereBetween('invoice_date', [$from, $to])
            ->sum('total_amount');


        // -------------------------
        // OUTGOINGS
        // -------------------------

        $purchases = Purchase::whereBetween('purchase_date', [$from, $to])
            ->sum('total_amount');

        $parts = Part::whereBetween('created_at', [$from, $to])
            ->sum('total_cost');

        $fuel = FuelLog::whereBetween('date', [$from, $to])
            ->sum('cost');


        // -------------------------
        // TOTALS
        // -------------------------

        $income = $sales;

        $outgoings =
            $purchases +
            $parts +
            $fuel;

        $profit = $income - $outgoings;

        $margin = $income > 0
            ? ($profit / $income) * 100
            : 0;


        return [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),

            'income' => [
                'sales' => round($sales, 2),
                'total' => round($income, 2),
            ],

            'outgoings' => [
                'purchases' => round($purchases, 2),
                'parts' => round($parts, 2),
                'fuel' => round($fuel, 2),
                'total' => round($outgoings, 2),
            ],

            'profit' => round($profit, 2),

            'margin' => round($margin, 1),
        ];
    }
}