<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConcernComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'concern_id',
        'user_id',
        'comment',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    /**
     * Get the concern
     */
    public function concern(): BelongsTo
    {
        return $this->belongsTo(Concern::class);
    }

    /**
     * Get the user who commented
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
