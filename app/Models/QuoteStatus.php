<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteStatus extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }
}
