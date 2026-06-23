<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    // Jika tabel kamu bernama "audit_logs", baris ini opsional (Laravel akan menebak dengan benar).
    // protected $table = 'audit_logs';

    protected $fillable = [
        'event',
        'user_id',
        'document_id',
        'document_version_id',
        'detail',
        'ip',
    ];

    // Simpan/muat kolom detail sebagai JSON array/object secara otomatis
    protected $casts = [
        'detail' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function document()
    {
        return $this->belongsTo(\App\Models\Document::class);
    }

    public function version()
    {
        return $this->belongsTo(\App\Models\DocumentVersion::class, 'document_version_id');
    }

    public function getHumanFriendlyDetailAttribute()
    {
        $detailText = '';
        
        // 1. Retrieve document & version snapshot from model relationships (for both standard & legacy fallbacks)
        $docCode = $this->document->doc_code ?? null;
        $docTitle = $this->document->title ?? null;
        $versionLabel = $this->version->version_label ?? null;

        // 2. Decode the detail array
        $decoded = $this->detail;
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        $actionSummary = null;

        if (is_array($decoded)) {
            // Overwrite snapshots if explicitly stored in new standardized payload
            if (isset($decoded['doc_code'])) $docCode = $decoded['doc_code'];
            if (isset($decoded['document_title'])) $docTitle = $decoded['document_title'];
            if (isset($decoded['version_label'])) $versionLabel = $decoded['version_label'];
            
            // Read action_summary if present (new payload)
            if (isset($decoded['action_summary'])) {
                $actionSummary = $decoded['action_summary'];
            } else {
                // Parse legacy payload
                $extra = [];
                foreach ($decoded as $k => $v) {
                    // Strip technical paths/files/directories
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
                        default => ucwords(str_replace('_', ' ', $k))
                    };
                    $extra[] = "{$friendlyK}: {$v}";
                }
                if (!empty($extra)) {
                    $actionSummary = implode(', ', $extra);
                }
            }
        }

        // 3. Construct the output text
        if ($docCode) {
            $detailText .= $docCode;
            if ($docTitle) {
                $detailText .= ' ' . $docTitle;
            }
            if ($versionLabel) {
                $detailText .= ' (Versi: ' . $versionLabel . ')';
            }
        }

        if ($actionSummary) {
            if ($detailText !== '') {
                $detailText .= ' | ';
            }
            $detailText .= $actionSummary;
        }

        // If still empty, fall back to event name
        if (empty($detailText)) {
            $detailText = ucwords(str_replace('_', ' ', $this->event));
        }

        return $detailText;
    }
}
