<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSegment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
