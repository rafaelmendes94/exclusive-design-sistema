<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualPriceRange extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:4',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
