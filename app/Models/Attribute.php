<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{

    protected $fillable = [
        'attribute_group_id',
        'name',
        'slug',
        'wordpress_term_id',
        'wordpress_slug',
        'wordpress_taxonomy',
        'wordpress_attribute_id',
    ];

    public function group()
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Attribute::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Attribute::class, 'parent_id');
    }

    public static function groupedByGroupSlugs(array $groups)
{
    return static::whereHas('group', function ($q) use ($groups) {
        $q->whereIn('slug', $groups);
    })
        ->with('group')
        ->get()
        ->groupBy(fn ($attr) => $attr->group->slug);
}
}
