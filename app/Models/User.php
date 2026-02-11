<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'perfect_attendance_bonus',
        'site_incentive',
        'attendance_incentive',
        'cola',
        'other_allowance',
        'date_hired',
        'birthday',
        'is_active',
        'site_id',
        'account_id',
        'payroll_group_id',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
        'department_id',
    ];

    /**
     * Get the profile photo URL checking multiple possible paths
     */
    public function getProfilePhotoUrl()
    {
        if (!$this->profile_photo) {
            return null;
        }

        // 1. Check if it's a full URL
        if (filter_var($this->profile_photo, FILTER_VALIDATE_URL)) {
            return $this->profile_photo;
        }

        // 2. Check public/storage path
        if (file_exists(public_path('storage/' . $this->profile_photo))) {
            return asset('storage/' . $this->profile_photo);
        }

        // 3. Check public/uploads path
        if (file_exists(public_path('uploads/' . $this->profile_photo))) {
            return asset('uploads/' . $this->profile_photo);
        }

        // 4. Default to storage asset
        return asset('storage/' . $this->profile_photo);
    }

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
        'perfect_attendance_bonus' => 'decimal:2',
        'site_incentive' => 'decimal:2',
        'attendance_incentive' => 'decimal:2',
        'cola' => 'decimal:2',
        'other_allowance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the payroll group the user belongs to
     */
    public function payrollGroup(): BelongsTo
    {
        return $this->belongsTo(PayrollGroup::class);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Check if user is HR
     */
    public function isHr(): bool
    {
        return in_array($this->role, ['hr', 'accounting', 'super_admin']);
    }

    /**
     * Check if user is Accounting
     */
    public function isAccounting(): bool
    {
        return $this->role === 'accounting' || $this->role === 'super_admin';
    }

    /**
     * Check if user is employee
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Get the hierarchy level of the user based on their account.
     */
    public function getHierarchyLevelAttribute(): int
    {
        return $this->account ? $this->account->hierarchy_level : 0;
    }

    /**
     * Determine if this user can manage another user based on hierarchy.
     */
    public function canManage(User $targetUser): bool
    {
        // Super Admin can manage anyone
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Target is Super Admin, but current user is not
        if ($targetUser->isSuperAdmin()) {
            return false;
        }

        // Can manage self
        if ($this->id === $targetUser->id) {
            return true;
        }

        // Higher hierarchy level can manage lower level
        return $this->hierarchy_level > $targetUser->hierarchy_level;
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

    public function assignedDepartment()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
