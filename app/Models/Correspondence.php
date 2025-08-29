<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class Correspondence extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'task_id',
        'type',
        'reference',
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
     * Get the options for the activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['task_id', 'type', 'reference'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the task tracking that owns the correspondence.
     */
    public function taskTracking(): BelongsTo
    {
        return $this->belongsTo(TasksTracking::class, 'task_id');
    }

    /**
     * Get the user who created the correspondence.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the correspondence.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the correspondence.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Query builder scope for filtering correspondences.
     */
    public static function allowedFilters(): array
    {
        return [
            'type',
            'reference',
            AllowedFilter::exact('task_id'),
            AllowedFilter::scope('by_task'),
            AllowedFilter::scope('by_type'),
        ];
    }

    /**
     * Query builder scope for sorting correspondences.
     */
    public static function allowedSorts(): array
    {
        return [
            'type',
            'reference',
            'task_id',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Scope for correspondences by task.
     */
    public function scopeByTask($query, $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Scope for correspondences by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for correspondences with task tracking.
     */
    public function scopeWithTaskTracking($query)
    {
        return $query->with('taskTracking');
    }

    /**
     * Scope for correspondences by reference search.
     */
    public function scopeByReference($query, $reference)
    {
        return $query->where('reference', 'like', "%{$reference}%");
    }

    /**
     * Get correspondences with their related data.
     */
    public static function withRelations()
    {
        return self::with(['taskTracking', 'createdBy', 'updatedBy']);
    }

    /**
     * Get correspondences summary statistics.
     */
    public static function getSummary()
    {
        return [
            'total' => self::count(),
            'by_type' => self::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get(),
            'by_task' => self::select('task_id', DB::raw('count(*) as count'))
                ->with('taskTracking:id,correspondence')
                ->groupBy('task_id')
                ->get(),
        ];
    }
}
