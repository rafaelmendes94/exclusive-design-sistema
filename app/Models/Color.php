<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(ColorGroup::class, 'color_group_id');
    }
}
