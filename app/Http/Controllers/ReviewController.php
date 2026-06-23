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
            $docCode = null;
            $docTitle = null;
            $versionLabel = null;

            if ($documentId) {
                $doc = \App\Models\Document::find($documentId);
                if ($doc) {
                    $docCode = $doc->doc_code;
                    $docTitle = $doc->title;
                }
            }

            if ($documentVersionId) {
                $ver = \App\Models\DocumentVersion::find($documentVersionId);
                if ($ver) {
                    $versionLabel = $ver->version_label;
                    if (!$docCode && $ver->document) {
                        $docCode = $ver->document->doc_code;
                        $docTitle = $ver->document->title;
                    }
                }
            }

            // Build action_summary
            $actionSummary = null;
            if (is_array($detail)) {
                $extra = [];
                foreach ($detail as $k => $v) {
                    if (in_array(strtolower($k), ['path', 'file', 'filename'])) {
                        continue;
                    }
                    if (is_array($v)) $v = json_encode($v);
                    elseif (is_bool($v)) $v = $v ? 'true' : 'false';

                    $friendlyK = match(strtolower($k)) {
                        'note' => 'Catatan',
                        'reason' => 'Alasan',
                        'stage' => 'Tahap',
                        'from' => 'Status Awal',
                        'summary' => 'Ringkasan',
                        'action_summary' => 'Ringkasan',
                        default => ucwords(str_replace('_', ' ', $k))
                    };
                    $extra[] = "{$friendlyK}: {$v}";
                }
                if (!empty($extra)) {
                    $actionSummary = implode(', ', $extra);
                }
            } elseif (is_string($detail)) {
                $actionSummary = $detail;
            }

            if (empty($actionSummary)) {
                $actionSummary = match($event) {
                    'create_baseline_publish' => 'Baseline document published',
                    'create_baseline_draft' => 'Baseline draft version created',
                    'create_replace_draft' => 'Replace draft version created',
                    'version_created' => 'New document version created',
                    'version_updated' => 'Document version updated',
                    'version_submitted' => 'Version submitted for approval',
                    'mr_forward_version' => 'Version forwarded to Director by MR',
                    'director_approve_version' => 'Version approved by Director',
                    'reject_version' => 'Version rejected',
                    'trash_version' => 'Version moved to Recycle Bin',
                    'restore_version' => 'Version restored from Recycle Bin',
                    'destroy_version' => 'Version permanently deleted',
                    'document_metadata_updated' => 'Metadata updated',
                    'document_review_still_relevant' => 'Document reviewed - still relevant',
                    'document_review_needs_revision' => 'Document reviewed - needs revision',
                    'move_to_recycle' => 'Moved to Recycle Bin',
                    'submit_draft' => 'Draft submitted for approval',
                    'reopen_draft' => 'Draft reopened',
                    default => ucwords(str_replace('_', ' ', $event))
                };
            }

            \App\Models\AuditLog::create([
                'event' => $event,
                'user_id' => $userId,
                'document_id' => $documentId,
                'document_version_id' => $documentVersionId,
                'detail' => json_encode([
                    'doc_code' => $docCode,
                    'document_title' => $docTitle,
                    'version_label' => $versionLabel,
                    'action_summary' => $actionSummary,
                ]),
                'ip' => $ip,
            ]);
        } catch (\Throwable) { /* ignore */ }
    }
}
