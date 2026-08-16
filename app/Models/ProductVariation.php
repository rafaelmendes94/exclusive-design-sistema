<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'cost_price' => 'decimal:4',
        'sale_price' => 'decimal:4',
        'raw_payload' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function primaryColor()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function secondaryColor()
    {
        return $this->belongsTo(Color::class, 'secondary_color_id');
    }
}
