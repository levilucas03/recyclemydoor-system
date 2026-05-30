<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeGroup extends Model
{

    protected $fillable = [
        'name',
        'slug',
        'wordpress_term_id',
        'wordpress_slug',
        'wordpress_taxonomy',
        'wordpress_attribute_id',
    ];

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }
}
