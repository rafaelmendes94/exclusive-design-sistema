<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactorTable extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function ranges()
    {
        return $this->hasMany(FactorRange::class)->orderBy('quantity_from');
    }

    public static function defaultMostExpensive(): ?self
    {
        return self::query()
            ->where('active', true)
            ->withMin('ranges', 'coefficient')
            ->orderByRaw('ranges_min_coefficient is null')
            ->orderBy('ranges_min_coefficient')
            ->first();
    }
}
