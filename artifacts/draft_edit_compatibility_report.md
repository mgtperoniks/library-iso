# Compatibility Report: Draft Edit View Reuse

---

## 1. Analysis

### Variables Passed by `DraftController@edit`
* `$version` (type: `App\Models\DocumentVersion`)
* `$document` (type: `App\Models\Document` via `$version->document`)
* `$departments` (type: `Collection` of `App\Models\Department`)
* `$categories` (type: `Collection` of `App\Models\Category` or `array`)

### Variables Required by `resources/views/versions/edit.blade.php`
* `$version` (accesses: `$version->version_label`, `$version->id`, `$version->file_path`, `$version->pasted_text`, `$version->change_note`, `$version->signed_by`, `$version->signed_at`)
* `$document` (accesses: `$document->doc_code`, `$document->id`)

---

## 2. Compatibility Result
**FULL**

### Evidence & Rationale
Every variable accessed by the template `versions/edit.blade.php` is supplied by the array returned in `DraftController@edit`. No additional variables are required by the view. Therefore, the `versions.edit` view can be reused directly by changing the view path in the controller from `'drafts.edit'` to `'versions.edit'`.
