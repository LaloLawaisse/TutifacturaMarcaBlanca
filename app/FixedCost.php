<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FixedCost extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'amount',
        'day_of_month',
        'next_run_date',
        'active',
    ];
}
