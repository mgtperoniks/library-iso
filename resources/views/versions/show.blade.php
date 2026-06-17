{{-- resources/views/versions/show.blade.php --}}
@extends('layouts.iso')

@section('title', ($document->doc_code ?? '') . ' - ' . ($version->version_label ?? 'Version'))

@section('content')
@php
    use Illuminate\Support\Facades\Storage;

    // defensive guards
    $document = $document ?? null;
    $version = $version ?? null;
    $otherVersions = $otherVersions ?? collect();
    $user = auth()->user();
@endphp

<div style="max-width:1000px;margin:auto;">
  @if($document && $document->current_version_id && $version && $version->id != $document->current_version_id && $version->status === 'approved')
    <div style="background-color: #fee4e2; border: 2px solid #b42318; color: #b42318; padding: 15px; text-align: center; font-weight: bold; font-size: 1.2rem; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        ⚠️ OBSOLETE VERSION — FOR REFERENCE ONLY ⚠️
    </div>
  @endif

  <h2>{{ $document->doc_code ?? '-' }} — {{ $document->title ?? '-' }}</h2>
  <p class="small-muted">
    Version: <strong>{{ $version->version_label ?? '-' }}</strong>
    — Status: <strong>{{ $version->status ?? '-' }}</strong>
  </p>

  <div style="display:flex;gap:16px;">
    <div style="flex:1">
      <div style="background:#fff;border:1px solid #eef3f8;border-radius:8px;padding:12px;margin-bottom:12px;">
        <h3>Version details</h3>

        <p><strong>Change note:</strong> {{ $version->change_note ?? '-' }}</p>
        <p><strong>Created by:</strong> {{ optional($version->creator)->name ?? $version->created_by ?? '-' }}</p>
        <p>
          <strong>Signed by:</strong>
          {{ $version->signed_by ?? '-' }}
          @if(!empty($version->signed_at))
            ({{ \Carbon\Carbon::parse($version->signed_at)->format('Y-m-d') }})
          @endif
        </p>
        <p><strong>Checksum:</strong>
          <code style="font-size:12px">{{ $version->checksum ?? '-' }}</code>
        </p>

        @php
          // file URL handling (prefer public disk; adjust if you use 'documents' disk)
          $fileUrl = null;
          if (!empty($version->file_path)) {
              try {
                  $fileUrl = Storage::disk('public')->url(ltrim($version->file_path, '/'));
              } catch (\Throwable $e) {
                  // fallback to asset if storage URL fails
                  $fileUrl = asset('storage/' . ltrim($version->file_path, '/'));
              }
          }
        @endphp

        @if(!empty($fileUrl))
          <div style="margin-top:10px;">
            <strong>File:</strong><br>
            <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer">Download file</a>
          </div>
        @else
          <div style="margin-top:10px;">
            <strong>Text content</strong>
            <pre style="white-space:pre-wrap;background:#f8fafc;padding:10px;border-radius:6px;">
{{ $version->pasted_text ?? $version->plain_text ?? '-' }}
            </pre>
          </div>
        @endif

        {{-- Approval History Timeline --}}
        @if(\Schema::hasTable('approval_logs'))
          @php
            $logs = \DB::table('approval_logs')
                ->where('document_version_id', $version->id)
                ->join('users', 'users.id', '=', 'approval_logs.user_id')
                ->select('approval_logs.*', 'users.name as user_name')
                ->orderBy('approval_logs.created_at', 'asc')
                ->get();
          @endphp
          <div style="margin-top:20px;padding-top:15px;border-top:1px solid #e2e8f0;">
            <h4 style="margin-top:0;margin-bottom:12px;color:#0b5ed7;">Approval History Timeline</h4>
            @if($logs->count() > 0)
              <div style="position:relative;padding-left:20px;border-left:2px solid #e2e8f0;margin-left:10px;">
                @foreach($logs as $log)
                  <div style="margin-bottom:12px;position:relative;">
                    <span style="position:absolute;left:-27px;top:4px;width:12px;height:12px;border-radius:50%;background:#0b5ed7;border:2px solid #fff;"></span>
                    <div style="font-weight:600;font-size:.9rem;color:#1e293b;">
                      {{ ucwords(str_replace('_', ' ', $log->action)) }}
                      <span style="font-weight:normal;color:#64748b;font-size:.8rem;">
                        by <strong>{{ $log->user_name }}</strong> ({{ ucwords($log->role) }})
                      </span>
                    </div>
                    @if($log->note)
                      <div style="font-size:.85rem;color:#475569;background:#f8fafc;padding:6px 10px;border-radius:6px;margin-top:4px;display:inline-block;border:1px solid #e2e8f0;">
                        {{ $log->note }}
                      </div>
                    @endif
                    <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;">
                      {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <p class="small-muted" style="font-style:italic;">No approval logs found for this version.</p>
            @endif
          </div>
        @endif

        <div style="margin-top:10px;">
          <a class="btn" href="{{ route('documents.show', $document->id ?? 0) }}">Back to Document</a>

          @php
            $canEdit = false;
            if ($user) {
                if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['kabag','mr','admin','director'])) {
                    $canEdit = true;
                } elseif (isset($version->created_by) && $user->id == $version->created_by) {
                    $canEdit = true;
                }
            }
          @endphp

          @if($canEdit)
            <a class="btn" href="{{ route('versions.edit', $version->id) }}">Edit Version</a>
          @endif
        </div>
      </div>
    </div>

    <div style="width:260px;">
      <div style="background:#fff;border:1px solid #eef3f8;border-radius:8px;padding:12px;">
        <h4>Other versions</h4>
        <ul style="list-style:none;padding:0;margin:0">
          @forelse($otherVersions as $ov)
            <li style="padding:6px 0;border-bottom:1px solid #f1f5f9">
              <a href="{{ route('versions.show', $ov->id) }}">{{ $ov->version_label }}</a><br>
              <small class="small-muted">{{ $ov->status ?? '-' }} — {{ $ov->created_at ? $ov->created_at->format('Y-m-d') : '-' }}</small>
            </li>
          @empty
            <li style="padding:6px 0;color:#6b7280">No other versions.</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>
</div>

{{-- Optional: notify opener window (useful when opening version from queue) --}}
<script>
(function(){
  try {
    var vid = "{{ $version->id ?? '' }}";
    if (vid && window.opener && !window.opener.closed) {
      try {
        window.opener.postMessage({ iso_action: 'version_opened', version_id: vid }, '*');
      } catch(e){}
      try { localStorage.setItem('iso_opened_version_' + vid, '1'); } catch(e){}
    }
  } catch(e){/* ignore errors */ }
})();
</script>
@endsection
