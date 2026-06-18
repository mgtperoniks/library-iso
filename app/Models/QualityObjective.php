<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityObjective extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'period_id',
        'department_id',
        'code',
        'process_name',
        'objective_statement',
        'kpi_indicator',
        'unit',
        'target_value',
        'target_polarity',
        'monitoring_frequency',
        'measurement_method',
        'pic_user_id',
        'status',
        'renewal_of_id',
        'is_mandatory',
        'sort_order',
        'notes',
        'created_by',
        'submitted_at',
        'activated_at',
        'closed_at',
    ];

    protected $casts = [
        'target_value' => 'float',
        'is_mandatory' => 'boolean',
        'sort_order' => 'integer',
        'submitted_at' => 'datetime',
        'activated_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /* -------------------------
     * Accessors (Computed)
     * ------------------------- */

    /**
     * Get the dynamically calculated achievement percentage from the latest monitoring record.
     */
    public function getAchievementPercentageAttribute(): ?float
    {
        $latest = $this->monitorings()
            ->whereNotNull('realization_value')
            ->latest('id')
            ->first();

        if (!$latest) {
            return null;
        }

        return (float) $latest->achievement_pct;
    }

    /**
     * Get the achievement status dynamically derived from the latest monitoring record.
     */
    public function getAchievementStatusAttribute(): string
    {
        $latest = $this->monitorings()
            ->whereNotNull('realization_value')
            ->latest('id')
            ->first();

        if (!$latest) {
            return 'not_reported';
        }

        $pct = $latest->achievement_pct;

        if ($pct === null) {
            return 'not_reported';
        }

        if ($pct >= 110.0) {
            return 'excellent';
        } elseif ($pct >= 100.0) {
            return 'on_track';
        } elseif ($pct >= 80.0) {
            return 'at_risk';
        } else {
            return 'off_track';
        }
    }

    /**
     * Get current achievement status alias.
     */
    public function getCurrentAchievementStatusAttribute(): string
    {
        return $this->achievement_status;
    }

    /**
     * Get current achievement percentage alias.
     */
    public function getCurrentAchievementPctAttribute(): ?float
    {
        return $this->achievement_percentage;
    }

    /**
     * Get overall achievement percentage (average of all submitted months).
     */
    public function getOverallAchievementPctAttribute(): ?float
    {
        $validMonitorings = $this->monitorings()->whereNotNull('realization_value')->get();
        if ($validMonitorings->isEmpty()) {
            return null;
        }
        return (float) round($validMonitorings->avg('achievement_pct'), 2);
    }

    /**
     * Get reporting compliance percentage based on elapsed months of the year.
     */
    public function getReportingCompliancePctAttribute(): float
    {
        $currentMonth = (int) now()->format('n'); // June (6)
        $expectedMonths = range(1, $currentMonth);
        
        $actualReported = $this->monitorings()
            ->where('period_year', 2026)
            ->whereIn('period_month', $expectedMonths)
            ->whereNotNull('realization_value')
            ->count();
            
        $totalExpected = count($expectedMonths);
        if ($totalExpected <= 0) return 100.0;
        
        return round(($actualReported / $totalExpected) * 100, 2);
    }

    /* -------------------------
     * Relationships
     * ------------------------- */

    public function period(): BelongsTo
    {
        return $this->belongsTo(QualityObjectivePeriod::class, 'period_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function renewalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'renewal_of_id');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(self::class, 'renewal_of_id');
    }

    public function actionPlans(): HasMany
    {
        return $this->hasMany(QualityObjectiveActionPlan::class, 'objective_id');
    }

    public function monitorings(): HasMany
    {
        return $this->hasMany(QualityObjectiveMonitoring::class, 'objective_id');
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(QualityObjectiveEvaluation::class, 'objective_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(QualityObjectiveApproval::class, 'objective_id');
    }

    /* -------------------------
     * Scopes
     * ------------------------- */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForDept($query, $deptId)
    {
        return $query->where('department_id', $deptId);
    }

    public function scopeForPeriod($query, $periodId)
    {
        return $query->where('period_id', $periodId);
    }
}
