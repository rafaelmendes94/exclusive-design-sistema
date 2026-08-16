<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function businessSegment()
    {
        return $this->belongsTo(BusinessSegment::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }
}
