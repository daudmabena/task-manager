<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Correspondence extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
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
     * Get the tasks tracking that owns the correspondence.
     */
    public function tasksTracking(): BelongsTo
    {
        return $this->belongsTo(TasksTracking::class, 'task_id');
    }

    /**
     * Get the user who created the correspondence.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the correspondence.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the correspondence.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // Scope Methods

    /**
     * Scope a query to only include correspondences for a specific task.
     */
    public function scopeForTask($query, $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Scope a query to only include correspondences with a specific type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include correspondences with email type.
     */
    public function scopeEmail($query)
    {
        return $query->where('type', 'email');
    }

    /**
     * Scope a query to only include correspondences with letter type.
     */
    public function scopeLetter($query)
    {
        return $query->where('type', 'letter');
    }

    /**
     * Scope a query to only include correspondences with phone type.
     */
    public function scopePhone($query)
    {
        return $query->where('type', 'phone');
    }

    /**
     * Scope a query to only include correspondences with meeting type.
     */
    public function scopeMeeting($query)
    {
        return $query->where('type', 'meeting');
    }

    /**
     * Scope a query to only include correspondences with document type.
     */
    public function scopeDocument($query)
    {
        return $query->where('type', 'document');
    }

    /**
     * Scope a query to only include correspondences created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to only include correspondences updated by a specific user.
     */
    public function scopeUpdatedBy($query, $userId)
    {
        return $query->where('updated_by', $userId);
    }

    /**
     * Scope a query to only include correspondences with a specific reference.
     */
    public function scopeByReference($query, $reference)
    {
        return $query->where('reference', 'like', "%{$reference}%");
    }

    /**
     * Scope a query to only include correspondences created within a date range.
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include correspondences updated within a date range.
     */
    public function scopeUpdatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('updated_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include recent correspondences.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to only include correspondences from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope a query to only include correspondences from this week.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope a query to only include correspondences from this month.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    /**
     * Scope a query to only include correspondences from this year.
     */
    public function scopeThisYear($query)
    {
        return $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
    }

    /**
     * Get the correspondence type options.
     */
    public static function getTypeOptions(): array
    {
        return [
            'email' => 'Email',
            'letter' => 'Letter',
            'phone' => 'Phone Call',
            'meeting' => 'Meeting',
            'document' => 'Document',
            'other' => 'Other',
        ];
    }

    /**
     * Get the correspondence type label.
     */
    public function getTypeLabelAttribute(): string
    {
        $types = self::getTypeOptions();
        return $types[$this->type] ?? $this->type;
    }

    /**
     * Check if the correspondence is an email.
     */
    public function getIsEmailAttribute(): bool
    {
        return $this->type === 'email';
    }

    /**
     * Check if the correspondence is a letter.
     */
    public function getIsLetterAttribute(): bool
    {
        return $this->type === 'letter';
    }

    /**
     * Check if the correspondence is a phone call.
     */
    public function getIsPhoneAttribute(): bool
    {
        return $this->type === 'phone';
    }

    /**
     * Check if the correspondence is a meeting.
     */
    public function getIsMeetingAttribute(): bool
    {
        return $this->type === 'meeting';
    }

    /**
     * Check if the correspondence is a document.
     */
    public function getIsDocumentAttribute(): bool
    {
        return $this->type === 'document';
    }

    /**
     * Get the correspondence icon based on type.
     */
    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'email' => 'Mail',
            'letter' => 'FileText',
            'phone' => 'Phone',
            'meeting' => 'Users',
            'document' => 'File',
            default => 'MessageSquare',
        };
    }

    /**
     * Get the correspondence color based on type.
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'email' => 'blue',
            'letter' => 'green',
            'phone' => 'purple',
            'meeting' => 'orange',
            'document' => 'red',
            default => 'gray',
        };
    }
}
