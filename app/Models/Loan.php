<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'total_amount',
        'remaining_balance',
        'monthly_amortization',
        'payment_start_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'payment_start_date' => 'date',
        'total_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'monthly_amortization' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
