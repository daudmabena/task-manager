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

class FunctionsRequirement extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    use LogsActivity;
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
     * Get the options for the activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['process_id', 'name', 'requirement', 'planned_start_date', 'planned_end_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the process that owns the function requirement.
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    /**
     * Get the tasks tracking for the function requirement.
     */
    public function tasksTracking(): HasMany
    {
        return $this->hasMany(TasksTracking::class, 'function_id');
    }

    /**
     * Get the user who created the function requirement.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the function requirement.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the function requirement.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Query builder scope for filtering function requirements.
     */
    public static function allowedFilters(): array
    {
        return [
            'name',
            'requirement',
            AllowedFilter::exact('process_id'),
            AllowedFilter::scope('by_process'),
            AllowedFilter::scope('upcoming'),
            AllowedFilter::scope('overdue'),
        ];
    }

    /**
     * Query builder scope for sorting function requirements.
     */
    public static function allowedSorts(): array
    {
        return [
            'name',
            'process_id',
            'planned_start_date',
            'planned_end_date',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Scope for function requirements by process.
     */
    public function scopeByProcess($query, $processId)
    {
        return $query->where('process_id', $processId);
    }

    /**
     * Scope for upcoming function requirements.
     */
    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('planned_start_date', '>=', now())
            ->where('planned_start_date', '<=', now()->addDays($days));
    }

    /**
     * Scope for overdue function requirements.
     */
    public function scopeOverdue($query)
    {
        return $query->where('planned_end_date', '<', now());
    }

    /**
     * Scope for function requirements with tasks tracking.
     */
    public function scopeWithTasksTracking($query)
    {
        return $query->with('tasksTracking');
    }

    /**
     * Scope for function requirements by name search.
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Get function requirements with their related data.
     */
    public static function withRelations()
    {
        return self::with(['process', 'tasksTracking', 'createdBy', 'updatedBy']);
    }

    /**
     * Get function requirements summary statistics.
     */
    public static function getSummary()
    {
        return [
            'total' => self::count(),
            'upcoming' => self::upcoming()->count(),
            'overdue' => self::overdue()->count(),
            'by_process' => self::select('process_id', DB::raw('count(*) as count'))
                ->with('process:id,name')
                ->groupBy('process_id')
                ->get(),
        ];
    }
}
