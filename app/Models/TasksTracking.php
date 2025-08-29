<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TasksTracking extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    use LogsActivity;
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
     * Get the options for the activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['function_id', 'correspondence', 'actual_start_date', 'actual_end_date', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the function requirement that owns the task tracking.
     */
    public function functionRequirement(): BelongsTo
    {
        return $this->belongsTo(FunctionsRequirement::class, 'function_id');
    }

    /**
     * Get the correspondences for the task tracking.
     */
    public function correspondences(): HasMany
    {
        return $this->hasMany(Correspondence::class, 'task_id');
    }

    /**
     * Get the user who created the task tracking.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the task tracking.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the task tracking.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Query builder scope for filtering task tracking.
     */
    public static function allowedFilters(): array
    {
        return [
            'correspondence',
            'status',
            AllowedFilter::exact('function_id'),
            AllowedFilter::scope('by_function'),
            AllowedFilter::scope('by_status'),
            AllowedFilter::scope('overdue'),
        ];
    }

    /**
     * Query builder scope for sorting task tracking.
     */
    public static function allowedSorts(): array
    {
        return [
            'correspondence',
            'status',
            'actual_start_date',
            'actual_end_date',
            'function_id',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Scope for task tracking by function.
     */
    public function scopeByFunction($query, $functionId)
    {
        return $query->where('function_id', $functionId);
    }

    /**
     * Scope for task tracking by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for overdue task tracking.
     */
    public function scopeOverdue($query)
    {
        return $query->where('actual_end_date', '<', now())
            ->where('status', '!=', 'completed');
    }

    /**
     * Scope for task tracking with correspondences.
     */
    public function scopeWithCorrespondences($query)
    {
        return $query->with('correspondences');
    }

    /**
     * Scope for task tracking by correspondence search.
     */
    public function scopeByCorrespondence($query, $correspondence)
    {
        return $query->where('correspondence', 'like', "%{$correspondence}%");
    }

    /**
     * Get task tracking with their related data.
     */
    public static function withRelations()
    {
        return self::with(['functionRequirement', 'correspondences', 'createdBy', 'updatedBy']);
    }

    /**
     * Get task tracking summary statistics.
     */
    public static function getSummary()
    {
        return [
            'total' => self::count(),
            'by_status' => self::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'overdue' => self::overdue()->count(),
            'by_function' => self::select('function_id', DB::raw('count(*) as count'))
                ->with('functionRequirement:id,name')
                ->groupBy('function_id')
                ->get(),
        ];
    }
}
