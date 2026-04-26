<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
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
            ]
        ]);
    }
}