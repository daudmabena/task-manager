<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class Task extends Model implements Auditable
{
    use AuditableTrait, LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'user_id',
        'assigned_to',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the options for the activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'description', 'status', 'priority', 'due_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user who created the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user assigned to the task.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Query builder scope for filtering tasks.
     */
    public static function allowedFilters(): array
    {
        return [
            'title',
            'status',
            'priority',
            AllowedFilter::exact('user_id'),
            AllowedFilter::exact('assigned_to'),
            AllowedFilter::scope('due_soon'),
        ];
    }

    /**
     * Query builder scope for sorting tasks.
     */
    public static function allowedSorts(): array
    {
        return [
            'title',
            'status',
            'priority',
            'due_date',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Scope for tasks due soon.
     */
    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days));
    }
}
