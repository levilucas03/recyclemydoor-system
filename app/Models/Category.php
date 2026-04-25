<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
    ];

    // parent
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // children
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // products
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'category_product',
            'category_id',
            'product_id'
        );
    }
}