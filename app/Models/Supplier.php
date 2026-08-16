<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'api_key' => 'encrypted',
        'api_secret' => 'encrypted',
    ];
}
