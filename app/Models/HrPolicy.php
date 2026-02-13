<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
        'is_published',
        'effective_date',
        'attachment_path',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'effective_date' => 'date',
    ];
}
