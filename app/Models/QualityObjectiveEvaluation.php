<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class QualityObjectiveEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'objective_id',
        'period_id',
        'avg_achievement_pct',
        'total_monitoring_count',
        'completed_action_count',
        'evaluation_result',
        'root_cause',
        'contributing_factors',
        'recommendation',
        'follow_up_action',
        'next_period_decision',
        'evaluated_by',
        'evaluated_at',
        'reviewed_by_dir',
        'reviewed_at_dir',
        'management_review_ref',
    ];

    protected $casts = [
        'avg_achievement_pct' => 'float',
        'total_monitoring_count' => 'integer',
        'completed_action_count' => 'integer',
        'evaluated_at' => 'datetime',
        'reviewed_at_dir' => 'datetime',
    ];

    /* -------------------------
     * Relationships
     * ------------------------- */

    public function objective(): BelongsTo
    {
        return $this->belongsTo(QualityObjective::class, 'objective_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(QualityObjectivePeriod::class, 'period_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_dir');
    }

    public function evidences(): MorphMany
    {
        return $this->morphMany(QualityObjectiveEvidence::class, 'reference', 'reference_type', 'reference_id');
    }
}
