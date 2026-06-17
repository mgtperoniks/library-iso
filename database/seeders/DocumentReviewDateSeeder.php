<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use Carbon\Carbon;

class DocumentReviewDateSeeder extends Seeder
{
    public function run(): void
    {
        $documents = Document::all();
        foreach ($documents as $doc) {
            $baseDate = $doc->revision_date ?? $doc->created_at ?? Carbon::now();
            $doc->update([
                'next_review_date' => Carbon::parse($baseDate)->addMonths($doc->review_frequency ?? 12),
            ]);
        }
    }
}
