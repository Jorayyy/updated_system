<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'department_id',
        'payroll_group_id',
        'category',
        'time_in',
        'time_out',
        'lunch_break_minutes',
        'first_break_minutes',
        'second_break_minutes',
        'registered_hours',
        'description',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function payrollGroup()
    {
        return $this->belongsTo(PayrollGroup::class);
    }
}
