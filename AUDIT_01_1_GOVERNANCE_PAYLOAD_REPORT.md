# AUDIT_01_1_GOVERNANCE_PAYLOAD_REPORT

## 1. Traced Event Payload Sources

Here is the audit of all codebase locations that write to the `audit_logs` table, including the current payloads and the standardized ideal payloads:

| Event Name | Controller / Source | Current Payload | Ideal Payload (Standardized) |
| --- | --- | --- | --- |
| **create_baseline_draft** | `DocumentController@store` | `[]` (empty) | `{"doc_code": "...", "document_title": "...", "version_label": "v1", "summary": "Baseline draft version created"}` |
| **create_replace_draft** | `DocumentController@createReplaceDraft` | `[]` (empty) | `{"doc_code": "...", "document_title": "...", "version_label": "vX", "summary": "Replace draft version created"}` |
| **document_metadata_updated** | `DocumentController@updateMetadata` | `{"summary": "Metadata updated"}` | `{"doc_code": "...", "document_title": "...", "version_label": null, "summary": "Metadata updated"}` |
| **upload_version** | `DocumentController@uploadPdf` | `{"file": "DP.MR.01/v1/..."}` | `{"doc_code": "...", "document_title": "...", "version_label": "v1", "summary": "Uploaded document version"}` |
| **version_created** | `DocumentVersionController@store` | `{"file": "..."}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "New document version created"}` |
| **version_updated** | `DocumentVersionController@update` | `{"file": "..."}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Document version updated"}` |
| **version_submitted** | `DocumentVersionController@submit` | `{"stage": "MR"}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Tahap: MR"}` |
| **mr_forward_version** | `ApprovalController@forward` | `{"stage": "DIRECTOR"}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Tahap: DIRECTOR"}` |
| **director_approve_version** | `ApprovalController@approve` | `[]` (empty) | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Version approved by Director"}` |
| **reject_version** | `DocumentController@reject` / `ApprovalController` | `{"reason": "Reason text"}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Alasan: Reason text"}` |
| **trash_version** | `DocumentController@trash` | `{"from": "..."}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Moved to Recycle Bin (Status Awal: ...)"}` |
| **restore_version** | `RecycleController@restore` | `{"note": "restored..."}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Restored version from Recycle Bin"}` |
| **destroy_version** | `RecycleController@destroy` | `{"note": "permanently..."}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Permanently deleted version"}` |
| **move_to_recycle** | `DraftController@destroy` | `{"from": "..."}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Moved to Recycle Bin (Status Awal: ...)"}` |
| **submit_draft** | `DraftController@submit` | `{"note": "submitted..."}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Draft submitted for approval"}` |
| **reopen_draft** | `DraftController@reopen` | `{"note": "reopened..."}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Draft reopened from recycle/rejected"}` |
| **document_review_still_relevant** | `ReviewController@stillRelevant` | `{"note": "note..."}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Catatan: note..."}` |
| **document_review_needs_revision** | `ReviewController@needsRevision` | `{"note": "note..."}` | `{"doc_code": "...", "document_title": "...", "version_label": "...", "summary": "Catatan: note..."}` |

---

## 2. Refactoring & Models Modified
*(Note: These will be completed and updated during execution)*
- **Modified Controllers:** `DocumentController`, `DocumentVersionController`, `ApprovalController`, `ReviewController`, `RecycleController`, `DraftController`, `AuditLogController`.
- **Modified Models:** `AuditLog`.

---

## 3. Human Friendly Output Samples
Below is how `AuditLog::getHumanFriendlyDetailAttribute()` formats the standardized data for the view:
- **Before Refactor (Raw Technical Path):**
  `Path: DP.MR.01/v1/1765187214_pdf_IzVl1r_DP.MR.01_STRUKTUR_ORGANISASI.pdf`
- **After Refactor (Standardized & Clean):**
  `DP.MR.01 STRUKTUR ORGANISASI (Versi: v3) | Metadata updated`
