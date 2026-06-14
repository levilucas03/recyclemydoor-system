<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartController extends Controller
{
    public function index()
    {
        return Inertia::render('parts/Index', [
            'parts' => Part::latest()->paginate(20),
        ]);
    }

    public function create()
    {
        return Inertia::render('parts/Create');
    }

    public function store(Request $request)
    {
        Part::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'total_quantity' => ['required', 'integer', 'min:1'],
            'total_cost' => ['required', 'numeric', 'min:0'],
            'purchased_at' => ['nullable', 'date'],
        ]));

        return redirect()->route('parts.index')
            ->with('success', 'Part created successfully.');
    }

    public function edit(Part $part)
    {
        return Inertia::render('parts/Edit', [
            'part' => $part,
        ]);
    }

    public function update(Request $request, Part $part)
    {
        $part->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'total_quantity' => ['required', 'integer', 'min:1'],
            'total_cost' => ['required', 'numeric', 'min:0'],
            'purchased_at' => ['nullable', 'date'],
        ]));

        return redirect()->route('parts.index')
            ->with('success', 'Part updated successfully.');
    }

    public function destroy(Part $part)
    {
        $part->delete();

        return redirect()->route('parts.index')
            ->with('success', 'Part deleted successfully.');
    }
}