<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewEvent extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'outcome',
        'note',
        'review_date',
    ];

    protected $casts = [
        'review_date' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
