<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\FuelLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FuelLogController extends Controller
{
    public function index(Request $request)
    {
        $query = FuelLog::with('vehicle');

        // ---------------------
        // DATE FILTERS
        // ---------------------
        if ($request->period === 'this_week') {
            $query->whereBetween('date', [
                now()->startOfWeek(),
                now()
            ]);
        }

        if ($request->period === 'last_week') {
            $query->whereBetween('date', [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek()
            ]);
        }

        if ($request->period === 'this_month') {
            $query->whereBetween('date', [
                now()->startOfMonth(),
                now()
            ]);
        }

        if ($request->period === 'last_month') {
            $query->whereBetween('date', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth()
            ]);
        }

        $fuelLogs = $query->latest('date')->paginate(15)->withQueryString();

        return Inertia::render('FuelLogs/Index', [
            'fuelLogs' => $fuelLogs,
            'filters' => $request->only('period'),
        ]);
    }

    public function create()
    {
        return Inertia::render('FuelLogs/Form', [
            'vehicles' => Vehicle::all(),
            'fuelLog' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'mileage' => 'nullable|integer',
            'litres' => 'nullable|numeric',
            'cost' => 'required|numeric',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        FuelLog::create($data);

        return redirect()->route('fuel-logs.index')->with('success', 'Fuel log added');
    }

    public function edit(FuelLog $fuelLog)
    {
        return Inertia::render('FuelLogs/Form', [
            'vehicles' => Vehicle::all(),
            'fuelLog' => $fuelLog,
        ]);
    }

    public function update(Request $request, FuelLog $fuelLog)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'mileage' => 'required|integer',
            'litres' => 'required|numeric',
            'cost' => 'required|numeric',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $fuelLog->update($data);

        return redirect()->route('fuel-logs.index')->with('success', 'Fuel log updated');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:fuel_logs,id',
        ]);

        FuelLog::whereIn('id', $request->ids)->delete();

        return redirect()->route('fuel-logs.index')->with('success', 'Selected fuel logs deleted successfully.');
    }
}