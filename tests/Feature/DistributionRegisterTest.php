<?php

use App\Models\User;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\DocumentDistributionLog;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Set up database roles
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'mr']);
    Role::firstOrCreate(['name' => 'viewer']);

    // Create a department with ID 1
    \App\Models\Department::create([
        'id' => 1,
        'code' => 'MR',
        'name' => 'Management Representative',
    ]);
});

test('unauthenticated users cannot view distribution register', function () {
    $response = $this->get(route('distribution.index'));
    $response->assertRedirect('/login');
});

test('viewer role is forbidden from viewing distribution register', function () {
    $user = User::factory()->create();
    $user->assignRole('viewer');

    $response = $this->actingAs($user)->get(route('distribution.index'));
    $response->assertStatus(403);
});

test('admin and mr roles can view distribution register', function () {
    $mr = User::factory()->create();
    $mr->assignRole('mr');

    $response = $this->actingAs($mr)->get(route('distribution.index'));
    $response->assertStatus(200);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('distribution.index'));
    $response->assertStatus(200);
});

test('downloading and previewing documents logs events correctly', function () {
    Storage::fake('documents');
    
    $document = Document::create([
        'doc_code' => 'DP-MR-01',
        'title' => 'Struktur Organisasi',
        'department_id' => 1,
    ]);

    $version = DocumentVersion::create([
        'document_id' => $document->id,
        'version_label' => 'v1',
        'pdf_path' => 'DP-MR-01/v1/test.pdf',
        'status' => 'approved',
    ]);

    // Create the dummy file in storage
    Storage::disk('documents')->put('DP-MR-01/v1/test.pdf', 'dummy pdf content');

    $mr = User::factory()->create();
    $mr->assignRole('mr');

    // Perform preview
    $response = $this->actingAs($mr)->get(route('documents.versions.preview', $version->id));
    $response->assertStatus(200);

    // Assert that distribution log was created
    $log = DocumentDistributionLog::where('document_id', $document->id)->first();
    expect($log)->not->toBeNull();
    expect($log->action)->toBe('preview_pdf');
    expect($log->doc_code)->toBe('DP-MR-01');
    expect($log->document_title)->toBe('Struktur Organisasi');
    expect($log->user_email)->toBe($mr->email);
    expect($log->trace_id)->toStartWith('DISTR-');
});

test('distribution register page supports filters and CSV export', function () {
    $mr = User::factory()->create();
    $mr->assignRole('mr');

    // Seed some logs
    DocumentDistributionLog::create([
        'document_id' => 1,
        'document_version_id' => 1,
        'doc_code' => 'DP-MR-01',
        'document_title' => 'Struktur Organisasi',
        'version_label' => 'v1',
        'user_name' => 'Alice',
        'user_email' => 'alice@test.com',
        'user_role' => 'mr',
        'user_department' => 'MR',
        'action' => 'preview_pdf',
        'trace_id' => 'DISTR-20260623-000001',
        'ip_address' => '127.0.0.1',
    ]);

    DocumentDistributionLog::create([
        'document_id' => 2,
        'document_version_id' => 2,
        'doc_code' => 'SOP-GUD-02',
        'document_title' => 'SOP Gudang',
        'version_label' => 'v2',
        'user_name' => 'Bob',
        'user_email' => 'bob@test.com',
        'user_role' => 'kabag',
        'user_department' => 'Gudang',
        'action' => 'download_master',
        'trace_id' => 'DISTR-20260623-000002',
        'ip_address' => '127.0.0.1',
    ]);

    // Test document filter
    $response = $this->actingAs($mr)->get(route('distribution.index', ['document' => 'SOP-GUD']));
    $response->assertStatus(200);
    $response->assertSee('SOP-GUD-02');
    $response->assertDontSee('DP-MR-01');

    // Test action/activity filter
    $response = $this->actingAs($mr)->get(route('distribution.index', ['action' => 'preview_pdf']));
    $response->assertStatus(200);
    $response->assertSee('DP-MR-01');
    $response->assertDontSee('SOP-GUD-02');

    // Test CSV export
    $response = $this->actingAs($mr)->get(route('distribution.index', ['export' => 'csv']));
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    expect($content)->toContain('DISTR-20260623-000001');
    expect($content)->toContain('Membuka PDF');
});
