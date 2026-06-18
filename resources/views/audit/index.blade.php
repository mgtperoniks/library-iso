@extends('layouts.iso')

@section('title', 'Audit Trail')

@section('content')
<h2>Audit Trail</h2>
<p>Recent file/version events (proxy for audit trail).</p>



<div style="background:#fff; border:1px solid #eef3f8; border-radius:10px; padding:15px; margin-bottom:18px;">
  <form method="GET" action="{{ route('audit.index') }}" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
    
    <div>
      <label for="filter_event" style="font-weight:600; font-size:.85rem; color:#4b5563; display:block; margin-bottom:4px;">Event</label>
      <input type="text" id="filter_event" name="event" value="{{ request('event') }}" placeholder="Event name..." style="padding:8px; border:1px solid #cbd5e1; border-radius:6px; font-size:.9rem; width:180px;">
    </div>

    <div>
      <label for="filter_user" style="font-weight:600; font-size:.85rem; color:#4b5563; display:block; margin-bottom:4px;">User (Email / Name)</label>
      <input type="text" id="filter_user" name="user" value="{{ request('user') }}" placeholder="Email or name..." style="padding:8px; border:1px solid #cbd5e1; border-radius:6px; font-size:.9rem; width:180px;">
    </div>

    <div>
      <label for="filter_doc" style="font-weight:600; font-size:.85rem; color:#4b5563; display:block; margin-bottom:4px;">Document Code</label>
      <input type="text" id="filter_doc" name="document" value="{{ request('document') }}" placeholder="Doc code..." style="padding:8px; border:1px solid #cbd5e1; border-radius:6px; font-size:.9rem; width:150px;">
    </div>

    <div>
      <label for="filter_start" style="font-weight:600; font-size:.85rem; color:#4b5563; display:block; margin-bottom:4px;">Start Date</label>
      <input type="date" id="filter_start" name="start_date" value="{{ request('start_date') }}" style="padding:8px; border:1px solid #cbd5e1; border-radius:6px; font-size:.9rem;">
    </div>

    <div>
      <label for="filter_end" style="font-weight:600; font-size:.85rem; color:#4b5563; display:block; margin-bottom:4px;">End Date</label>
      <input type="date" id="filter_end" name="end_date" value="{{ request('end_date') }}" style="padding:8px; border:1px solid #cbd5e1; border-radius:6px; font-size:.9rem;">
    </div>

    <div style="display:flex; gap:8px;">
      <button type="submit" class="btn btn-primary"><span class="material-symbols-outlined" style="font-size:18px;">filter_alt</span> Apply</button>
      <a href="{{ route('audit.index') }}" class="btn btn-secondary"><span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span> Reset</a>
      <a class="btn btn-outline" href="{{ route('audit.index', array_merge(request()->query(), ['export' => 'csv'])) }}"><span class="material-symbols-outlined" style="font-size:18px;">download</span> Export CSV</a>
    </div>

  </form>
</div>

@if($events->count() === 0)
  <p class="muted">No audit events.</p>
@else
  <table class="table">
    <thead>
      <tr>
        <th>When</th>
        <th>Event</th>
        <th>Doc</th>
        <th>Version</th>
        <th>User</th>
        <th>Details</th>
        <th>IP</th>
      </tr>
    </thead>
    <tbody>
      @foreach($events as $e)
        <tr>
          <td>{{ $e->created_at?->format('Y-m-d H:i') }}</td>
          <td>{{ $e->event }}</td>
          <td style="font-family: var(--mono); white-space: nowrap;">{{ $e->document->doc_code ?? '-' }}</td>
          <td>{{ $e->version->version_label ?? '-' }}</td>
          <td>{{ $e->user->email ?? $e->user->name ?? '-' }}</td>
          <td>
            @php
              $detailText = '-';
              if ($e->detail) {
                  $decoded = is_string($e->detail) ? json_decode($e->detail, true) : $e->detail;
                  if (is_array($decoded)) {
                      $parts = [];
                      foreach ($decoded as $key => $val) {
                          if (is_array($val)) {
                              $val = json_encode($val);
                          } elseif (is_bool($val)) {
                              $val = $val ? 'true' : 'false';
                          }
                          $parts[] = ucwords(str_replace('_', ' ', $key)) . ': ' . $val;
                      }
                      $detailText = implode(', ', $parts);
                  } else {
                      $detailText = (string)$e->detail;
                  }
              }
              $short = Str::limit($detailText, 120);
            @endphp
            {{ $short ?: '-' }}
          </td>
          <td>{{ $e->ip ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  @if(method_exists($events, 'links'))
    <div style="margin-top:12px;">
      {{ $events->links() }}
    </div>
  @endif
@endif
@endsection
