<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraService extends Model
{
    protected $fillable = [
        'name',
        'description',
        'applies_to',
        'price_adult',
        'price_child',
        'charge_type',
        'is_active',
        'child_age_min',
        'child_age_max',
    ];
}
