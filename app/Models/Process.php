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

class Process extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    use SoftDeletes;

    protected $fillable = [
        'system_id',
        'name',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the system that owns the process.
     */
    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    /**
     * Get the functions requirements for the process.
     */
    public function functionsRequirements(): HasMany
    {
        return $this->hasMany(FunctionsRequirement::class);
    }

    /**
     * Get the user who created the process.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the process.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the process.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // Scope Methods

    /**
     * Scope a query to only include processes for a specific system.
     */
    public function scopeForSystem($query, $systemId)
    {
        return $query->where('system_id', $systemId);
    }

    /**
     * Scope a query to only include processes with a specific name.
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Scope a query to only include processes created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to only include processes updated by a specific user.
     */
    public function scopeUpdatedBy($query, $userId)
    {
        return $query->where('updated_by', $userId);
    }

    /**
     * Scope a query to only include processes created within a date range.
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include processes updated within a date range.
     */
    public function scopeUpdatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('updated_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include processes with functions requirements.
     */
    public function scopeWithFunctionsRequirements($query)
    {
        return $query->has('functionsRequirements');
    }

    /**
     * Scope a query to only include processes without functions requirements.
     */
    public function scopeWithoutFunctionsRequirements($query)
    {
        return $query->doesntHave('functionsRequirements');
    }

    /**
     * Scope a query to only include processes with a specific number of functions requirements.
     */
    public function scopeWithFunctionsRequirementsCount($query, $count)
    {
        return $query->withCount('functionsRequirements')->having('functions_requirements_count', $count);
    }

    /**
     * Scope a query to only include processes with functions requirements count greater than.
     */
    public function scopeWithFunctionsRequirementsCountGreaterThan($query, $count)
    {
        return $query->withCount('functionsRequirements')->having('functions_requirements_count', '>', $count);
    }

    /**
     * Scope a query to only include processes with functions requirements count less than.
     */
    public function scopeWithFunctionsRequirementsCountLessThan($query, $count)
    {
        return $query->withCount('functionsRequirements')->having('functions_requirements_count', '<', $count);
    }

    /**
     * Get the total count of functions requirements for this process.
     */
    public function getFunctionsRequirementsCountAttribute()
    {
        return $this->functionsRequirements()->count();
    }

    /**
     * Get the total count of tasks tracking for this process.
     */
    public function getTasksTrackingCountAttribute()
    {
        return $this->functionsRequirements()
            ->withCount('tasksTracking')
            ->get()
            ->sum('tasks_tracking_count');
    }

    /**
     * Get the total count of completed tasks for this process.
     */
    public function getCompletedTasksCountAttribute()
    {
        return $this->functionsRequirements()
            ->whereHas('tasksTracking', function ($query) {
                $query->where('status', 'completed');
            })
            ->count();
    }

    /**
     * Get the total count of pending tasks for this process.
     */
    public function getPendingTasksCountAttribute()
    {
        return $this->functionsRequirements()
            ->whereHas('tasksTracking', function ($query) {
                $query->where('status', 'pending');
            })
            ->count();
    }

    /**
     * Get the total count of in-progress tasks for this process.
     */
    public function getInProgressTasksCountAttribute()
    {
        return $this->functionsRequirements()
            ->whereHas('tasksTracking', function ($query) {
                $query->where('status', 'in_progress');
            })
            ->count();
    }
}
