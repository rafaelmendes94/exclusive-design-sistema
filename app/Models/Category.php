<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'show_in_menu' => 'boolean',
        'update_items_price_table' => 'boolean',
        'featured' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function categoryFactorTable()
    {
        return $this->belongsTo(FactorTable::class, 'category_factor_table_id');
    }
}
