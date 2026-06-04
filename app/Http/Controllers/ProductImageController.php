<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function reorder(Request $request, Product $product)
    {
        $validated = $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['integer', 'exists:product_images,id'],
        ]);

        foreach ($validated['images'] as $index => $imageId) {
            ProductImage::where('id', $imageId)
                ->where('product_id', $product->id)
                ->update([
                    'sort_order' => $index + 1,
                    'is_primary' => $index === 0,
                ]);
        }

        return back()->with('success', 'Image order updated.');
    }

    public function destroy(ProductImage $image)
    {
        $product = $image->product;

        $image->delete();

        $product->images()->update(['is_primary' => false]);

        $firstImage = $product->images()
            ->orderBy('sort_order')
            ->first();

        if ($firstImage) {
            $firstImage->update([
                'is_primary' => true,
                'sort_order' => 1,
            ]);
        }

        return back()->with('success', 'Image deleted.');
    }
}