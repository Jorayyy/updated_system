<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'site_id', 'is_active'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
