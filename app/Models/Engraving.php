<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Engraving extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function priceRanges()
    {
        return $this->hasMany(EngravingPriceRange::class)->orderBy('quantity_from');
    }
}
