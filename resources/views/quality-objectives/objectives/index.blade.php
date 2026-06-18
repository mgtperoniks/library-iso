@extends('layouts.iso')

@section('title', 'Daftar Sasaran Mutu')

@section('content')
<div class="site-header" style="margin-bottom: 24px;">
    <div class="brand">
        <div class="brand-text">
            <h1>Sasaran Mutu (Quality Objectives)</h1>
            <p class="sub">ISO 9001:2015 Clause 6.2 - Perencanaan Sasaran Mutu & Tindakan Pencapaian</p>
        </div>
    </div>
    @auth
        @if(auth()->user()->hasAnyRole(['admin', 'kabag']))
            <div>
                <a href="{{ route('quality-objectives.objectives.create') }}" class="btn btn-primary">
                    <span class="material-symbols-outlined">add</span>
                    Tambah Sasaran Mutu
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

{{-- ============================================================
     1. KPI DASHBOARD CARDS
     ============================================================ --}}
<div class="kpi-row">
    <div class="kpi-card" style="border-left: 4px solid var(--accent);">
        <p class="kpi-label">Total Sasaran</p>
        <p class="kpi-value">{{ $totalObjectives }}</p>
    </div>
    <div class="kpi-card" style="border-left: 4px solid #10b981;">
        <p class="kpi-label">Excellent</p>
        <p class="kpi-value" style="color: #065f46;">{{ $countExcellent }}</p>
    </div>
    <div class="kpi-card" style="border-left: 4px solid #22c55e;">
        <p class="kpi-label">On Track</p>
        <p class="kpi-value" style="color: #15803d;">{{ $countOnTrack }}</p>
    </div>
    <div class="kpi-card" style="border-left: 4px solid #eab308;">
        <p class="kpi-label">At Risk</p>
        <p class="kpi-value" style="color: #a16207;">{{ $countAtRisk }}</p>
    </div>
    <div class="kpi-card" style="border-left: 4px solid #ef4444;">
        <p class="kpi-label">Off Track</p>
        <p class="kpi-value" style="color: #991b1b;">{{ $countOffTrack }}</p>
    </div>
    <div class="kpi-card" style="border-left: 4px solid #64748b;">
        <p class="kpi-label">Not Reported</p>
        <p class="kpi-value" style="color: #64748b;">{{ $countNotReported }}</p>
    </div>
    <div class="kpi-card" style="border-left: 4px solid #6366f1;">
        <p class="kpi-label">Kepatuhan Lapor</p>
        <p class="kpi-value">{{ $avgCompliance }}%</p>
    </div>
</div>

{{-- ============================================================
     2. FILTER PANEL
     ============================================================ --}}
<div class="card card-section card-inner" style="margin-bottom: 20px; padding: 16px;">
    <form method="GET" action="{{ route('quality-objectives.objectives.index') }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
        <div style="flex: 1; min-width: 180px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--muted); display: block; margin-bottom: 6px;">Periode Tahun</label>
            <select name="period_id" class="form-input" style="padding: 8px;" onchange="this.form.submit()">
                <option value="">-- Pilih Periode --</option>
                @foreach($periods as $period)
                    <option value="{{ $period->id }}" {{ request('period_id') == $period->id ? 'selected' : '' }}>
                        Tahun {{ $period->year }} ({{ $period->title }})
                    </option>
                @endforeach
            </select>
        </div>

        @if(!auth()->user()->hasAnyRole(['kabag']))
            <div style="flex: 1; min-width: 220px;">
                <label style="font-size: 12px; font-weight: 600; color: var(--muted); display: block; margin-bottom: 6px;">Departemen</label>
                <select name="department_id" class="form-input" style="padding: 8px;" onchange="this.form.submit()">
                    <option value="">-- Semua Departemen --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            [{{ $dept->code }}] {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <a href="{{ route('quality-objectives.objectives.index') }}" class="btn btn-secondary" style="padding: 9px 16px;">Reset</a>
        </div>
    </form>
</div>

{{-- ============================================================
     3. OBJECTIVES LIST
     ============================================================ --}}
<div class="card card-section card-inner" style="padding: 0; overflow: hidden;">
    <table class="table">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Departemen</th>
                <th>Sasaran Mutu (Target & KPI)</th>
                <th>Achievement (Kini)</th>
                <th>Achievement (Rerata)</th>
                <th style="text-align: center;">Tren</th>
                <th>Status Kinerja</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($objectives as $obj)
                <tr>
                    <td>
                        <a href="{{ route('quality-objectives.objectives.show', $obj->id) }}">
                            <strong>{{ $obj->code }}</strong>
                        </a>
                    </td>
                    <td>
                        <span class="badge" style="background:#ededf9; color:#434655;">{{ $obj->department->code }}</span>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--on-surface);">{{ $obj->process_name }}</div>
                        <div style="font-size: 12px; color: var(--muted); max-width: 420px; white-space: normal;">
                            {{ Str::limit($obj->objective_statement, 120) }}
                        </div>
                        <div style="font-size: 11px; margin-top: 4px; color: var(--accent);">
                            <strong>KPI:</strong> {{ $obj->kpi_indicator }} (Target: {{ $obj->target_value }} {{ $obj->unit }})
                        </div>
                    </td>
                    <td>
                        @if($obj->current_achievement_pct !== null)
                            <strong>{{ number_format($obj->current_achievement_pct, 1) }}%</strong>
                        @else
                            <span style="color: var(--muted); font-style: italic;">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($obj->overall_achievement_pct !== null)
                            {{ number_format($obj->overall_achievement_pct, 1) }}%
                        @else
                            <span style="color: var(--muted); font-style: italic;">N/A</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-size: 18px;">
                        @include('quality-objectives._partials._trend_icon', ['trend' => $obj->trend_direction])
                    </td>
                    <td>
                        @include('quality-objectives._partials._status_badge', ['status' => $obj->current_achievement_status])
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        <a href="{{ route('quality-objectives.objectives.show', $obj->id) }}" class="btn btn-sm btn-outline">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 32px; color: var(--muted);">
                        Tidak ditemukan sasaran mutu untuk filter periode atau departemen ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 16px;">
    {{ $objectives->links() }}
</div>
@endsection
