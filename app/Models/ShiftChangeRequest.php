<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'approver_id',
        'requested_date',
        'current_schedule',
        'new_schedule',
        'reason',
        'status',
        'admin_remarks',
    ];

    protected $casts = [
        'requested_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
