<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'is_primary',
        'sort_order',
        'alt_text',
        'file_hash',
        'wordpress_image_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}