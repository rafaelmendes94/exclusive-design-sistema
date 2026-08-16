<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactorRange extends Model
{
    protected $guarded = [];

    protected $appends = [
        'product_factor_percent',
    ];

    protected $casts = [
        'coefficient' => 'decimal:4',
    ];

    public function getProductFactorPercentAttribute(): float
    {
        $coefficient = (float) $this->coefficient;

        return $coefficient > 0 ? round(100 / $coefficient, 4) : 0;
    }

    public function factorTable()
    {
        return $this->belongsTo(FactorTable::class);
    }
}
