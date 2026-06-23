<?php

use App\Models\User;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

test('access events are excluded from the audit trail index page', function () {
    $user = User::factory()->create();
    $user->assignRole('admin'); // Ensure they have access

    // Create some test events
    AuditLog::create([
        'event' => 'preview_pdf',
        'user_id' => $user->id,
        'detail' => ['path' => 'DP.MR.01/v1/somefile.pdf'],
    ]);

    AuditLog::create([
        'event' => 'download_master',
        'user_id' => $user->id,
        'detail' => ['path' => 'DP.MR.01/v1/master.docx'],
    ]);

    AuditLog::create([
        'event' => 'version_submitted',
        'user_id' => $user->id,
        'detail' => [
            'doc_code' => 'DP.MR.01',
            'document_title' => 'STRUKTUR ORGANISASI',
            'version_label' => 'v1',
            'action_summary' => 'Submitted for approval',
        ],
    ]);

    $response = $this->actingAs($user)->get(route('audit.index'));
    $response->assertStatus(200);

    // Verify version_submitted is visible, but preview_pdf & download_master are NOT visible
    $response->assertSee('version_submitted');
    $response->assertSee('STRUKTUR ORGANISASI');
    $response->assertSee('Submitted for approval');

    $response->assertDontSee('preview_pdf');
    $response->assertDontSee('download_master');
});

test('human friendly accessor formats standardized detail correctly', function () {
    $log = new AuditLog([
        'event' => 'version_submitted',
        'detail' => [
            'doc_code' => 'DP.MR.01',
            'document_title' => 'STRUKTUR ORGANISASI',
            'version_label' => 'v2',
            'action_summary' => 'Submitted for approval',
        ],
    ]);

    $friendlyText = $log->human_friendly_detail;
    expect($friendlyText)->toBe('DP.MR.01 STRUKTUR ORGANISASI (Versi: v2) | Submitted for approval');
});

test('human friendly accessor handles legacy payloads safely and strips technical file paths', function () {
    // 1) Legacy detail containing file paths
    $log1 = new AuditLog([
        'event' => 'version_created',
        'detail' => [
            'file' => 'DP.MR.01/v1/1765187214_pdf_IzVl1r_DP.MR.01_STRUKTUR_ORGANISASI.pdf',
        ],
    ]);

    // Since there's no doc_code or summary, and 'file' is skipped, it should fall back safely to event name
    expect($log1->human_friendly_detail)->toBe('Version Created');

    // 2) Legacy detail containing metadata updates with summary key
    $log2 = new AuditLog([
        'event' => 'document_metadata_updated',
        'detail' => [
            'summary' => 'Metadata updated',
        ],
    ]);
    expect($log2->human_friendly_detail)->toBe('Ringkasan: Metadata updated');
});
