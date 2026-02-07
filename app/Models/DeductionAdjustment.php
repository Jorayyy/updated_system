<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeductionAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payroll_period_id',
        'deduction_code',
        'description',
        'amount',
        'effective_date',
        'added_by',
        'is_processed'
    ];

    protected $casts = [
        'effective_date' => 'datetime',
        'is_processed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
