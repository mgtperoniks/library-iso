@extends('layouts.iso')

@section('title', 'Periode Sasaran Mutu')

@section('content')
<div class="site-header">
    <div class="brand">
        <div class="brand-text">
            <h1>Periode Sasaran Mutu</h1>
            <p class="sub">ISO 9001:2015 Clause 6.2</p>
        </div>
    </div>
    @auth
        @if(auth()->user()->hasAnyRole(['admin', 'mr']))
            <div>
                <a href="{{ route('quality-objectives.periods.create') }}" class="btn btn-primary">
                    <span class="material-symbols-outlined">add</span>
                    Tambah Periode
                </a>
            </div>
        @endif
    @endauth
</div>

@if(session('success'))
    <div class="card bg-primary-subtle" style="padding: 12px; margin-bottom: 16px; border-left: 4px solid var(--accent);">
        <p style="margin: 0; font-size: 14px; font-weight: 600; color: var(--accent);">{{ session('success') }}</p>
    </div>
@endif

@if(session('error'))
    <div class="card" style="padding: 12px; margin-bottom: 16px; border-left: 4px solid var(--error); background: var(--error-container);">
        <p style="margin: 0; font-size: 14px; font-weight: 600; color: var(--error);">{{ session('error') }}</p>
    </div>
@endif

<div class="card card-section card-inner" style="padding: 0; overflow: hidden;">
    <table class="table">
        <thead>
            <tr>
                <th>Tahun</th>
                <th>Judul Periode</th>
                <th>Status</th>
                <th>Jumlah Sasaran Mutu</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($periods as $period)
                <tr>
                    <td><strong>{{ $period->year }}</strong></td>
                    <td>{{ $period->title }}</td>
                    <td>
                        @include('quality-objectives._partials._status_badge', ['status' => $period->status])
                    </td>
                    <td>{{ $period->objectives_count }} data</td>
                    <td style="text-align: right;">
                        @auth
                            @if(auth()->user()->hasAnyRole(['admin', 'mr']))
                                <a href="{{ route('quality-objectives.periods.edit', $period->id) }}" class="btn btn-sm btn-outline" style="margin-right: 6px;">
                                    Edit
                                </a>
                                <form action="{{ route('quality-objectives.periods.destroy', $period->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus periode ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            @else
                                <span class="small-muted">No actions</span>
                            @endif
                        @endauth
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 24px; color: var(--muted);">
                        Belum ada data periode yang dibuat.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 16px;">
    {{ $periods->links() }}
</div>
@endsection
