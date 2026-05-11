<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Purchase;
use App\Models\Product;
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
        ]);
    }
}