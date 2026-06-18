<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityObjectivePeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'title',
        'description',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'locked_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'approved_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    /* -------------------------
     * Relationships
     * ------------------------- */

    public function objectives(): HasMany
    {
        return $this->hasMany(QualityObjective::class, 'period_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /* -------------------------
     * Scopes
     * ------------------------- */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
