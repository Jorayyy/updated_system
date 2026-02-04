<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'work_start_time',
        'work_end_time',
        'break_duration_minutes',
        'is_active'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
