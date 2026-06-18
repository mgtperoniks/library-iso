<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class QualityObjectiveMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'objective_id',
        'period_label',
        'period_year',
        'period_month',
        'period_quarter',
        'target_snapshot',
        'realization_value',
        'achievement_pct',
        'data_source',
        'input_by',
        'input_at',
        'reviewed_by',
        'reviewed_at',
        'is_locked',
        'notes',
        'capa_triggered',
        'capa_ref_id',
    ];

    protected $casts = [
        'period_year' => 'integer',
        'period_month' => 'integer',
        'period_quarter' => 'integer',
        'target_snapshot' => 'float',
        'realization_value' => 'float',
        'achievement_pct' => 'float',
        'input_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'is_locked' => 'boolean',
        'capa_triggered' => 'boolean',
    ];

    /* -------------------------
     * Accessors (Computed)
     * ------------------------- */

    /**
     * Get the achievement status dynamically derived from the achievement percentage.
     */
    public function getAchievementStatusAttribute(): string
    {
        if ($this->realization_value === null || $this->achievement_pct === null) {
            return 'not_reported';
        }

        $pct = (float) $this->achievement_pct;

        if ($pct >= 100.0) {
            return 'on_track';
        } elseif ($pct >= 80.0) {
            return 'at_risk';
        } else {
            return 'off_track';
        }
    }

    /* -------------------------
     * Relationships
     * ------------------------- */

    public function objective(): BelongsTo
    {
        return $this->belongsTo(QualityObjective::class, 'objective_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function evidences(): MorphMany
    {
        return $this->morphMany(QualityObjectiveEvidence::class, 'reference', 'reference_type', 'reference_id');
    }

    /* -------------------------
     * Scopes
     * ------------------------- */

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }
}
