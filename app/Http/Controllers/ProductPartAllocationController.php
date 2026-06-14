<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Product;
use App\Models\ProductPartAllocation;
use Illuminate\Http\Request;

class ProductPartAllocationController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'part_id' => ['required', 'exists:parts,id'],
            'quantity_used' => ['required', 'integer', 'min:1'],
        ]);

        $part = Part::with('allocations')->findOrFail($data['part_id']);

        $available = $part->total_quantity - $part->allocations->sum('quantity_used');

        if ($data['quantity_used'] > $available) {
            return back()->withErrors([
                'quantity_used' => 'Not enough quantity available.',
            ]);
        }

        ProductPartAllocation::create([
            'product_id' => $product->id,
            'part_id' => $part->id,
            'quantity_used' => $data['quantity_used'],
            'unit_cost' => $part->unit_cost,
            'cost_allocated' => $part->unit_cost * $data['quantity_used'],
        ]);

        return back()->with('success', 'Part allocated to product.');
    }

    public function destroy(Product $product, ProductPartAllocation $allocation)
    {
        if ($allocation->product_id !== $product->id) {
            abort(403);
        }

        $allocation->delete();

        return back()->with('success', 'Part removed from product.');
    }
}