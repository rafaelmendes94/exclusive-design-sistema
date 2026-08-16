<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'cost_price' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'unit_price_2' => 'decimal:4',
        'unit_price_3' => 'decimal:4',
        'subtotal' => 'decimal:4',
        'subtotal_2' => 'decimal:4',
        'subtotal_3' => 'decimal:4',
        'freight' => 'decimal:4',
        'extra_percent' => 'decimal:4',
        'tax_percent' => 'decimal:4',
        'engraving_cost' => 'decimal:4',
        'calculation_snapshot' => 'array',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function factorTable()
    {
        return $this->belongsTo(FactorTable::class);
    }

    public function engraving()
    {
        return $this->belongsTo(Engraving::class);
    }
}
