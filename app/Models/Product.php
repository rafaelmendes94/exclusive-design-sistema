<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'block_supplier_update' => 'boolean',
        'use_manual_price_table' => 'boolean',
        'cost_price' => 'decimal:4',
        'sale_price' => 'decimal:4',
        'weight' => 'decimal:3',
        'height' => 'decimal:3',
        'width' => 'decimal:3',
        'depth' => 'decimal:3',
        'thickness' => 'decimal:3',
        'length' => 'decimal:3',
        'circumference' => 'decimal:3',
        'diameter' => 'decimal:3',
        'youtube_active' => 'boolean',
        'supplier_updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function factorTable()
    {
        return $this->belongsTo(FactorTable::class);
    }

    public function splash()
    {
        return $this->belongsTo(Splash::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function manualPriceRanges()
    {
        return $this->hasMany(ManualPriceRange::class);
    }

    public function engravings()
    {
        return $this->belongsToMany(Engraving::class, 'product_engraving')->withTimestamps();
    }
}
