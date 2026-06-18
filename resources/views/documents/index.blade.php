@extends('layouts.iso')

@section('content')

{{-- Header + Quick Action --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
  <h2 style="margin:0">Documents</h2>
  <div>
    <a class="btn btn-primary" href="{{ route('documents.create') }}"><span class="material-symbols-outlined" style="font-size:18px;">add</span> New Document</a>
  </div>
</div>

{{-- Filter Form --}}
<form method="get" action="{{ route('documents.index') }}" style="margin-bottom:12px;">
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <input
      type="text"
      name="search"
      placeholder="search doc code, title, or text"
      value="{{ request('search') }}"
      style="min-width:240px;"
    >

    <select name="department" style="min-width:200px;">
      <option value="">-- All Departments --</option>
      @foreach($departments as $d)
        <option value="{{ $d->code }}" {{ request('department') == $d->code ? 'selected' : '' }}>
          {{ $d->code }} - {{ $d->name }}
        </option>
      @endforeach
    </select>

    <button class="btn btn-primary" type="submit">Filter</button>

    <a href="{{ route('documents.index') }}" class="btn btn-secondary" style="margin-left:6px;">
      Reset
    </a>
  </div>
</form>

{{-- Documents Table --}}
<table class="table">
  <thead>
    <tr>
      <th style="white-space:nowrap;">Doc Code</th>
      <th>Title</th>
      <th style="white-space:nowrap;">Dept</th>
      <th style="white-space:nowrap;">Revision</th>
      <th style="white-space:nowrap;">Latest</th>
    </tr>
  </thead>

  <tbody>
  @forelse($docs as $d)
    <tr>
      {{-- Code --}}
      <td style="font-family:var(--mono);white-space:nowrap;">
        {{ $d->doc_code }}
      </td>

      {{-- Title --}}
      <td>
        <a href="{{ route('documents.show', $d->id) }}">{{ $d->title }}</a>
      </td>

      {{-- Dept --}}
      <td style="white-space:nowrap;">
        {{ $d->department->code ?? '-' }}
      </td>

      {{-- Revision (with safe date formatting) --}}
      <td style="white-space:nowrap;">
        {{ $d->revision_number ?? 0 }}
        <div style="font-size:12px;color:var(--muted);">
          @php
            $dt = $d->revision_date ?? null;
            if (!is_null($dt) && !($dt instanceof \Carbon\Carbon)) {
                try { $dt = \Illuminate\Support\Carbon::parse($dt); }
                catch (\Throwable $e) { $dt = null; }
            }
          @endphp
          {{ $dt ? $dt->format('Y-m-d') : '-' }}
        </div>
      </td>

      {{-- Latest Version --}}
      <td style="white-space:nowrap;">
        @if($d->currentVersion)
          <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">

            {{-- Version label --}}
            <span>{{ $d->currentVersion->version_label }}</span>

            {{-- Status badge --}}
            @switch($d->currentVersion->status)
              @case('approved')
                <span class="status-badge status-approved">approved</span>
                @break
              @case('rejected')
                <span class="status-badge status-rejected">rejected</span>
                @break
              @default
                <span class="status-badge status-submitted">{{ $d->currentVersion->status }}</span>
            @endswitch

            {{-- Text badge --}}
            @if($d->currentVersion->pasted_text)
              <span class="badge status-review">pasted</span>
            @elseif($d->currentVersion->plain_text)
              <span class="badge status-approved">indexed</span>
            @else
              <span class="badge status-draft">no-text</span>
            @endif

          </div>
        @else
          <span style="color:var(--muted)">-</span>
        @endif
      </td>
    </tr>

  @empty
    <tr>
      <td colspan="5" style="text-align:center;color:var(--muted);padding:16px;">
        No documents found.
      </td>
    </tr>
  @endforelse
  </tbody>
</table>

<div style="margin-top:12px;">
  {{ $docs->links() }}
</div>

@endsection
