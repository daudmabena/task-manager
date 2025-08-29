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

class Process extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    use LogsActivity;
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
     * Get the options for the activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['system_id', 'name', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the process.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the process.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Query builder scope for filtering processes.
     */
    public static function allowedFilters(): array
    {
        return [
            'name',
            'description',
            AllowedFilter::exact('system_id'),
            AllowedFilter::scope('by_system'),
            AllowedFilter::scope('recent'),
        ];
    }

    /**
     * Query builder scope for sorting processes.
     */
    public static function allowedSorts(): array
    {
        return [
            'name',
            'system_id',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Scope for processes by system.
     */
    public function scopeBySystem($query, $systemId)
    {
        return $query->where('system_id', $systemId);
    }

    /**
     * Scope for recent processes.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for processes with functions requirements.
     */
    public function scopeWithFunctionsRequirements($query)
    {
        return $query->with('functionsRequirements');
    }

    /**
     * Scope for processes by name search.
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Get processes with their related data.
     */
    public static function withRelations()
    {
        return self::with(['system', 'functionsRequirements', 'createdBy', 'updatedBy']);
    }

    /**
     * Get processes summary statistics.
     */
    public static function getSummary()
    {
        return [
            'total' => self::count(),
            'by_system' => self::select('system_id', DB::raw('count(*) as count'))
                ->with('system:id,name')
                ->groupBy('system_id')
                ->get(),
            'recent_count' => self::recent()->count(),
        ];
    }
}
