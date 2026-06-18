<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class QualityObjectiveActionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'objective_id',
        'sequence',
        'program_name',
        'description',
        'pic_user_id',
        'target_date',
        'actual_date',
        'budget_estimated',
        'progress_pct',
        'status',
        'completed_at',
        'completion_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'target_date' => 'date',
        'actual_date' => 'date',
        'budget_estimated' => 'float',
        'progress_pct' => 'integer',
        'completed_at' => 'datetime',
    ];

    /* -------------------------
     * Relationships
     * ------------------------- */

    public function objective(): BelongsTo
    {
        return $this->belongsTo(QualityObjective::class, 'objective_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function evidences(): MorphMany
    {
        return $this->morphMany(QualityObjectiveEvidence::class, 'reference', 'reference_type', 'reference_id');
    }

    /* -------------------------
     * Scopes
     * ------------------------- */

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('target_date', '<', now()->toDateString());
    }
}
