<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'profile_photo',
        'password',
        'role',
        'department',
        'position',
        'hourly_rate',
        'daily_rate',
        'monthly_salary',
        'meal_allowance',
        'transportation_allowance',
        'communication_allowance',
        'date_hired',
        'birthday',
        'is_active',
        'site_id',
        'account_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_hired' => 'date',
        'birthday' => 'date',
        'hourly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
        'meal_allowance' => 'decimal:2',
        'transportation_allowance' => 'decimal:2',
        'communication_allowance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is HR
     */
    public function isHr(): bool
    {
        return $this->role === 'hr';
    }

    /**
     * Check if user is employee
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    /**
     * Get user's attendances
     */
    /**
     * Get the site that the user belongs to.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the account that the user belongs to.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function schedule()
    {
        // Assuming an account has one primary active schedule
        return $this->hasOneThrough(Schedule::class, Account::class, 'id', 'account_id', 'account_id', 'id')
            ->where('schedules.is_active', true);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get user's leave requests
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get user's leave balances
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get user's payrolls
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get user's daily time records
     */
    public function dailyTimeRecords(): HasMany
    {
        return $this->hasMany(DailyTimeRecord::class);
    }

    /**
     * Get user's payslips
     */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    /**
     * Get user's notifications
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications count
     */
    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    /**
     * Get today's attendance record
     */
    public function todayAttendance()
    {
        return $this->attendances()->whereDate('date', today())->first();
    }

    /**
     * Get today's DTR record
     */
    public function todayDtr()
    {
        return $this->dailyTimeRecords()->whereDate('date', today())->first();
    }
}
