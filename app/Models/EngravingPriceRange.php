<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EngravingPriceRange extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:4',
    ];

    public function engraving()
    {
        return $this->belongsTo(Engraving::class);
    }
}
