<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\ReviewEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function due(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['kabag','admin','mr','director'])) {
            abort(403, 'Unauthorized.');
        }

        $documents = Document::whereNotNull('current_version_id')
            ->where(function ($query) {
                $query->whereNull('next_review_date')
                      ->orWhere('next_review_date', '<=', Carbon::now());
            })
            ->with(['department', 'currentVersion'])
            ->paginate(25);

        return view('reviews.due', compact('documents'));
    }

    public function stillRelevant(Request $request, Document $document)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['kabag','admin','mr','director'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $note = $request->input('note');

        ReviewEvent::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'outcome' => 'still_relevant',
            'note' => $note,
            'review_date' => Carbon::now(),
        ]);

        $document->update([
            'next_review_date' => Carbon::now()->addMonths($document->review_frequency ?? 12),
        ]);

        $this->maybeAudit('document_review_still_relevant', $user->id, $document->id, $document->current_version_id, $request->ip(), ['note' => $note]);

        return redirect()->back()->with('success', 'Dokumen ditandai masih relevan dan dijadwalkan ulang.');
    }

    public function needsRevision(Request $request, Document $document)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['kabag','admin','mr','director'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $note = $request->input('note');

        ReviewEvent::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'outcome' => 'needs_revision',
            'note' => $note,
            'review_date' => Carbon::now(),
        ]);

        $this->maybeAudit('document_review_needs_revision', $user->id, $document->id, $document->current_version_id, $request->ip(), ['note' => $note]);

        return redirect()->route('documents.show', [$document->id, 'edit' => '1'])->with('success', 'Review selesai: Revisi diperlukan. Silakan buat draf versi baru untuk dokumen ini.');
    }

    protected function maybeAudit(string $event, $userId = null, $documentId = null, $documentVersionId = null, $ip = null, $detail = [])
    {
        if (! class_exists(\App\Models\AuditLog::class)) return;
        try {
            \App\Models\AuditLog::create([
                'event' => $event,
                'user_id' => $userId,
                'document_id' => $documentId,
                'document_version_id' => $documentVersionId,
                'detail' => json_encode($detail),
                'ip' => $ip,
            ]);
        } catch (\Throwable) { /* ignore */ }
    }
}
