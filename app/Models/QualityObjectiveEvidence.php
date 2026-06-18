<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QualityObjectiveEvidence extends Model
{
    use HasFactory;

    protected $table = 'quality_objective_evidences';

    protected $fillable = [
        'reference_type',
        'reference_id',
        'file_name',
        'file_path',
        'document_id',
        'file_size',
        'mime_type',
        'disk',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /* -------------------------
     * Relationships
     * ------------------------- */

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
}
