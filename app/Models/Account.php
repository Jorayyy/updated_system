<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'description', 'site_id', 'hierarchy_level', 'system_role', 'is_active'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function activeSchedule()
    {
        return $this->hasOne(Schedule::class)->where('is_active', true);
    }
}
