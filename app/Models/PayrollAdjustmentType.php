<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollAdjustmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'target_field',
        'default_formula',
        'is_system_default',
    ];
}
