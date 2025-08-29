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

class TasksTracking extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    use SoftDeletes;

    protected $fillable = [
        'function_id',
        'correspondence',
        'actual_start_date',
        'actual_end_date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the functions requirement that owns the tasks tracking.
     */
    public function functionsRequirement(): BelongsTo
    {
        return $this->belongsTo(FunctionsRequirement::class, 'function_id');
    }

    /**
     * Get the correspondences for the tasks tracking.
     */
    public function correspondences(): HasMany
    {
        return $this->hasMany(Correspondence::class, 'task_id');
    }

    /**
     * Get the user who created the tasks tracking.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the tasks tracking.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the tasks tracking.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // Scope Methods

    /**
     * Scope a query to only include tasks tracking for a specific function.
     */
    public function scopeForFunction($query, $functionId)
    {
        return $query->where('function_id', $functionId);
    }

    /**
     * Scope a query to only include tasks tracking with a specific status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include tasks tracking with pending status.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include tasks tracking with in_progress status.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include tasks tracking with completed status.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include tasks tracking with cancelled status.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include tasks tracking created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to only include tasks tracking updated by a specific user.
     */
    public function scopeUpdatedBy($query, $userId)
    {
        return $query->where('updated_by', $userId);
    }

    /**
     * Scope a query to only include tasks tracking with actual start date in the future.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('actual_start_date', '>', now());
    }

    /**
     * Scope a query to only include tasks tracking with actual start date in the past.
     */
    public function scopeStarted($query)
    {
        return $query->where('actual_start_date', '<', now());
    }

    /**
     * Scope a query to only include tasks tracking with actual end date in the past.
     */
    public function scopeFinished($query)
    {
        return $query->where('actual_end_date', '<', now());
    }

    /**
     * Scope a query to only include tasks tracking with actual end date in the future.
     */
    public function scopeNotFinished($query)
    {
        return $query->where('actual_end_date', '>', now());
    }

    /**
     * Scope a query to only include tasks tracking within a date range.
     */
    public function scopeStartedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('actual_start_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include tasks tracking ending within a date range.
     */
    public function scopeEndingBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('actual_end_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include tasks tracking with correspondences.
     */
    public function scopeWithCorrespondences($query)
    {
        return $query->has('correspondences');
    }

    /**
     * Scope a query to only include tasks tracking without correspondences.
     */
    public function scopeWithoutCorrespondences($query)
    {
        return $query->doesntHave('correspondences');
    }

    /**
     * Scope a query to only include tasks tracking with a specific number of correspondences.
     */
    public function scopeWithCorrespondencesCount($query, $count)
    {
        return $query->withCount('correspondences')->having('correspondences_count', $count);
    }

    /**
     * Scope a query to only include tasks tracking with correspondences count greater than.
     */
    public function scopeWithCorrespondencesCountGreaterThan($query, $count)
    {
        return $query->withCount('correspondences')->having('correspondences_count', '>', $count);
    }

    /**
     * Scope a query to only include tasks tracking with correspondences count less than.
     */
    public function scopeWithCorrespondencesCountLessThan($query, $count)
    {
        return $query->withCount('correspondences')->having('correspondences_count', '<', $count);
    }

    /**
     * Get the total count of correspondences for this tasks tracking.
     */
    public function getCorrespondencesCountAttribute()
    {
        return $this->correspondences()->count();
    }

    /**
     * Check if the tasks tracking is overdue.
     */
    public function getIsOverdueAttribute()
    {
        return $this->actual_start_date < now() && $this->actual_end_date > now() && $this->status !== 'completed';
    }

    /**
     * Check if the tasks tracking is completed.
     */
    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the tasks tracking is pending.
     */
    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the tasks tracking is in progress.
     */
    public function getIsInProgressAttribute()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the tasks tracking is cancelled.
     */
    public function getIsCancelledAttribute()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get the duration in days.
     */
    public function getDurationDaysAttribute()
    {
        if (!$this->actual_start_date || !$this->actual_end_date) {
            return null;
        }
        return $this->actual_start_date->diffInDays($this->actual_end_date);
    }

    /**
     * Get the progress percentage based on current date.
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->status === 'completed') {
            return 100;
        }

        if ($this->status === 'cancelled') {
            return 0;
        }

        if (!$this->actual_start_date || !$this->actual_end_date) {
            return 0;
        }

        if ($this->actual_start_date > now()) {
            return 0;
        }

        if ($this->actual_end_date < now()) {
            return 100;
        }

        $totalDays = $this->actual_start_date->diffInDays($this->actual_end_date);
        $elapsedDays = $this->actual_start_date->diffInDays(now());

        return min(100, round(($elapsedDays / $totalDays) * 100, 2));
    }
}
