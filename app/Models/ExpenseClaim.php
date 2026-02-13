<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_incurred',
        'amount',
        'category',
        'description',
        'attachment_path',
        'status',
        'approved_by',
        'approved_at',
        'rejection_remarks'
    ];

    protected $casts = [
        'date_incurred' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
