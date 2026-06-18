<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityObjectiveApproval extends Model
{
    use HasFactory;

    // approval log is immutable, we only insert
    public $timestamps = false;

    protected $fillable = [
        'objective_id',
        'user_id',
        'role',
        'action',
        'stage',
        'note',
        'ip_address',
    ];

    /* -------------------------
     * Relationships
     * ------------------------- */

    public function objective(): BelongsTo
    {
        return $this->belongsTo(QualityObjective::class, 'objective_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
