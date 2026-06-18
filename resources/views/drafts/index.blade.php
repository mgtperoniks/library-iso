{{-- resources/views/drafts/index.blade.php --}}
@extends('layouts.iso')

@section('title', 'Drafts')

@section('content')
<div class="page-card">
    <h2 class="h2">Draft Container</h2>

    <div style="margin-bottom:12px; display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('drafts.index', ['filter' => 'draft']) }}" class="btn btn-secondary" aria-pressed="{{ request('filter') === 'draft' ? 'true' : 'false' }}"><span class="material-symbols-outlined" style="font-size:18px;">draft</span> Drafts</a>
        <a href="{{ route('drafts.index', ['filter' => 'rejected']) }}" class="btn btn-secondary" aria-pressed="{{ request('filter') === 'rejected' ? 'true' : 'false' }}"><span class="material-symbols-outlined" style="font-size:18px;">cancel</span> Rejected</a>
        <a href="{{ route('documents.create') }}" class="btn btn-primary"><span class="material-symbols-outlined" style="font-size:18px;">add</span> New Document</a>
    </div>

    <table class="table" role="table" aria-describedby="drafts-table">
        <thead>
            <tr>
                <th>Doc Code</th>
                <th>Title</th>
                <th>Version</th>
                <th>Creator</th>
                <th>Status</th>
                <th>Rejected Reason</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($drafts as $v)
            <tr>
                <td style="font-family: var(--mono); white-space: nowrap;">{{ $v->document->doc_code ?? '-' }}</td>
                <td>{{ $v->document->title ?? '-' }}</td>
                <td>{{ $v->version_label }}</td>
                <td>{{ $v->creator->name ?? '-' }}</td>
                <td>
                    <span class="status-badge {{ $v->status === 'rejected' ? 'status-rejected' : 'status-draft' }}">
                        {{ ucfirst($v->status) }}
                    </span>
                </td>

                <td style="max-width:240px;">
                    @if(!empty($v->rejected_reason))
                        {{ \Illuminate\Support\Str::limit($v->rejected_reason, 120) }}
                    @else
                        -
                    @endif
                </td>

                <td>
                    <a href="{{ route('drafts.show', $v->id) }}" class="btn btn-secondary"><span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span> Open</a>

                    @can('edit', $v)
                        <a href="{{ route('drafts.edit', $v->id) }}" class="btn btn-primary"><span class="material-symbols-outlined" style="font-size:16px;">edit</span> Edit</a>
                    @endcan

                    @if($v->status !== 'submitted')
                        {{-- Submit (POST) --}}
                        <form action="{{ route('drafts.submit', $v->id) }}" method="POST" style="display:inline">
                            @csrf
                            <button class="btn btn-success" type="submit" onclick="return confirm('Submit ke MR?')"><span class="material-symbols-outlined" style="font-size:16px;">send</span> Submit</button>
                        </form>

                        {{-- Delete (keputusan: tetap POST supaya sesuai route yang ada) --}}
                        <form action="{{ route('drafts.destroy', $v->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Hapus draft?')">
                            @csrf
                            <button class="btn btn-danger" type="submit"><span class="material-symbols-outlined" style="font-size:16px;">delete</span> Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No drafts found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">
        {{ $drafts->withQueryString()->links() }}
    </div>
</div>
@endsection
