<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FunctionsRequirement extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    use SoftDeletes;

    protected $fillable = [
        'process_id',
        'name',
        'requirement',
        'planned_start_date',
        'planned_end_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the process that owns the functions requirement.
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    /**
     * Get the tasks tracking for the functions requirement.
     */
    public function tasksTracking(): HasMany
    {
        return $this->hasMany(TasksTracking::class, 'function_id');
    }

    /**
     * Get the user who created the functions requirement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the functions requirement.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the functions requirement.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // Scope Methods

    /**
     * Scope a query to only include functions requirements for a specific process.
     */
    public function scopeForProcess($query, $processId)
    {
        return $query->where('process_id', $processId);
    }

    /**
     * Scope a query to only include functions requirements with a specific name.
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Scope a query to only include functions requirements created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to only include functions requirements updated by a specific user.
     */
    public function scopeUpdatedBy($query, $userId)
    {
        return $query->where('updated_by', $userId);
    }

    /**
     * Scope a query to only include functions requirements with planned start date in the future.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('planned_start_date', '>', now());
    }

    /**
     * Scope a query to only include functions requirements with planned start date in the past.
     */
    public function scopeOverdue($query)
    {
        return $query->where('planned_start_date', '<', now());
    }

    /**
     * Scope a query to only include functions requirements with planned end date in the future.
     */
    public function scopeNotCompleted($query)
    {
        return $query->where('planned_end_date', '>', now());
    }

    /**
     * Scope a query to only include functions requirements with planned end date in the past.
     */
    public function scopeCompleted($query)
    {
        return $query->where('planned_end_date', '<', now());
    }

    /**
     * Scope a query to only include functions requirements within a date range.
     */
    public function scopePlannedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('planned_start_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include functions requirements ending within a date range.
     */
    public function scopeEndingBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('planned_end_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include functions requirements with tasks tracking.
     */
    public function scopeWithTasksTracking($query)
    {
        return $query->has('tasksTracking');
    }

    /**
     * Scope a query to only include functions requirements without tasks tracking.
     */
    public function scopeWithoutTasksTracking($query)
    {
        return $query->doesntHave('tasksTracking');
    }

    /**
     * Scope a query to only include functions requirements with a specific number of tasks tracking.
     */
    public function scopeWithTasksTrackingCount($query, $count)
    {
        return $query->withCount('tasksTracking')->having('tasks_tracking_count', $count);
    }

    /**
     * Scope a query to only include functions requirements with tasks tracking count greater than.
     */
    public function scopeWithTasksTrackingCountGreaterThan($query, $count)
    {
        return $query->withCount('tasksTracking')->having('tasks_tracking_count', '>', $count);
    }

    /**
     * Scope a query to only include functions requirements with tasks tracking count less than.
     */
    public function scopeWithTasksTrackingCountLessThan($query, $count)
    {
        return $query->withCount('tasksTracking')->having('tasks_tracking_count', '<', $count);
    }

    /**
     * Get the total count of tasks tracking for this functions requirement.
     */
    public function getTasksTrackingCountAttribute()
    {
        return $this->tasksTracking()->count();
    }

    /**
     * Get the total count of completed tasks for this functions requirement.
     */
    public function getCompletedTasksCountAttribute()
    {
        return $this->tasksTracking()->where('status', 'completed')->count();
    }

    /**
     * Get the total count of pending tasks for this functions requirement.
     */
    public function getPendingTasksCountAttribute()
    {
        return $this->tasksTracking()->where('status', 'pending')->count();
    }

    /**
     * Get the total count of in-progress tasks for this functions requirement.
     */
    public function getInProgressTasksCountAttribute()
    {
        return $this->tasksTracking()->where('status', 'in_progress')->count();
    }

    /**
     * Check if the functions requirement is overdue.
     */
    public function getIsOverdueAttribute()
    {
        return $this->planned_start_date < now() && $this->planned_end_date > now();
    }

    /**
     * Check if the functions requirement is completed.
     */
    public function getIsCompletedAttribute()
    {
        return $this->planned_end_date < now();
    }

    /**
     * Check if the functions requirement is upcoming.
     */
    public function getIsUpcomingAttribute()
    {
        return $this->planned_start_date > now();
    }

    /**
     * Get the duration in days.
     */
    public function getDurationDaysAttribute()
    {
        return $this->planned_start_date->diffInDays($this->planned_end_date);
    }

    /**
     * Get the progress percentage based on current date.
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->planned_start_date > now()) {
            return 0;
        }

        if ($this->planned_end_date < now()) {
            return 100;
        }

        $totalDays = $this->planned_start_date->diffInDays($this->planned_end_date);
        $elapsedDays = $this->planned_start_date->diffInDays(now());

        return min(100, round(($elapsedDays / $totalDays) * 100, 2));
    }
}
