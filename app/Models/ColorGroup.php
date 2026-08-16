<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColorGroup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function colors()
    {
        return $this->hasMany(Color::class);
    }
}
