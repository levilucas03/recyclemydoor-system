<?php

namespace App\Http\Controllers;

use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductAttributeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'entity' => 'required|string',
            'product_id' => 'required|exists:products,id',
        ]);

        $attribute = ProductAttribute::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'entity' => $validated['entity'],
            'product_id' => $validated['product_id'],
        ]);

        return response()->json($attribute, 201);
    }

    public function index()
    {
        return ProductAttribute::with('product')->get();
    }
}
