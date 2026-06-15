<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ProductStatus;
use App\Models\Sale;
use App\Models\SaleItem;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'width',
        'height',
        'depth',
        'status',
        'sku',
        'notes',
        'description',
        'quantity',
        'brand_id',
        'user_id',
        'qty',
    ];

    protected $casts = [
        'status' => ProductStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Product.php
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_product')->withTimestamps();
    }

    public function brand()
    {
        return $this->belongsTo(Attribute::class, 'brand_id');
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function getPurchasePriceAttribute()
    {
        return $this->prices->firstWhere('type', 'purchase')?->price;
    }

    public function getPrice($type)
    {
        return $this->prices->where('type', $type)->first()?->price;
    }

    public function getAttributeIdForGroup(string $groupSlug): ?int
    {
        $attribute = $this->attributes
            ->first(fn ($attr) => optional($attr->group)->slug === $groupSlug);

        return $attribute?->id;
    }

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'category_product', // 🔥 pivot table
            'product_id',       // 🔥 this model key
            'category_id'       // 🔥 related key
        );
    }

    public function loadAttributeGroupIds(array $groups): static
    {
        $this->loadMissing('attributes.group');

        $productAttributes = collect($this->getRelation('attributes'))
            ->keyBy(fn ($attr) => $attr->group?->slug);

        foreach ($groups as $group) {

            $field = str_replace('-', '_', $group) . '_id';

            $this->setAttribute(
                $field,
                optional($productAttributes->get($group))->id
            );
        }

        return $this;
    }

    public function configuration()
    {
        return $this->belongsTo(Attribute::class, 'configuration_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    protected static function booted()
    {
        static::created(function ($product) {

            // Generate SKU: YY + padded ID
            $sku = now()->format('y') . str_pad($product->id, 4, '0', STR_PAD_LEFT);

            $product->update([
                'sku' => $sku
            ]);
        });

        static::creating(function ($product) {
            if (!$product->user_id && auth()->check()) {
                $product->user_id = auth()->id();
            }
        });
    }

    public function websiteListingLink()
    {
        return ListingPlatformLink::whereHas('listing.products', function ($query) {
            $query->where('products.id', $this->id);
        })->first();
    }

    public function partAllocations()
    {
        return $this->hasMany(ProductPartAllocation::class);
    }

    public function getRefurbCostAttribute()
    {
        return $this->partAllocations->sum('cost_allocated');
    }

    public function saleItem()
    {
        return $this->hasOne(SaleItem::class);
    }

    public function sale()
    {
        return $this->hasOneThrough(
            Sale::class,
            SaleItem::class,
            'product_id',
            'id',
            'id',
            'sale_id'
        );
    }

    public function ebayOrderItems()
    {
        return $this->hasMany(EbayOrderItem::class);
    }
}
