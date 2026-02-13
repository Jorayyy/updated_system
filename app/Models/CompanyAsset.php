<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'asset_code',
        'name',
        'type',
        'serial_number',
        'description',
        'purchase_date',
        'value',
        'status',
        'assigned_date',
        'remarks',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'assigned_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
