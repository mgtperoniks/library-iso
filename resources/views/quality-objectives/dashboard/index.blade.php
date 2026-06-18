@extends('layouts.iso')

@section('title', 'QMS Control Tower')

@section('content')
<div class="site-header" style="margin-bottom: 16px;">
    <div class="brand">
        <div class="brand-text">
            <h1>QMS Control Tower</h1>
            <p class="sub">ISO 9001:2015 Clause 6.2 - Pemantauan Pengecualian & Deviasi Mutu</p>
        </div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <form method="GET" action="{{ route('quality-objectives.dashboard') }}" id="periodForm">
            <select name="period_id" class="form-input" style="padding: 8px; width: 200px;" onchange="this.form.submit()">
                @foreach($periods as $period)
                    <option value="{{ $period->id }}" {{ $selectedPeriodId == $period->id ? 'selected' : '' }}>
                        Tahun {{ $period->year }} ({{ $period->title }})
                    </option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('quality-objectives.objectives.index') }}" class="btn btn-secondary">
            Detail Sasaran Mutu
        </a>
    </div>
</div>

{{-- Decision #6: Compact Primary KPI Cards --}}
<div class="kpi-row-compact">
    <div class="kpi-card-compact" style="border-left: 4px solid var(--accent);">
        <p class="kpi-label">Total Sasaran</p>
        <p class="kpi-value">{{ $totalCount }}</p>
    </div>
    <div class="kpi-card-compact" style="border-left: 4px solid #ef4444;">
        <p class="kpi-label">Off Track</p>
        <p class="kpi-value" style="color: #991b1b;">{{ $countOffTrack }}</p>
    </div>
    <div class="kpi-card-compact" style="border-left: 4px solid #eab308;">
        <p class="kpi-label">At Risk</p>
        <p class="kpi-value" style="color: #a16207;">{{ $countAtRisk }}</p>
    </div>
    <div class="kpi-card-compact" style="border-left: 4px solid #6366f1;">
        <p class="kpi-label">Kepatuhan Lapor</p>
        <p class="kpi-value">{{ $avgCompliance }}%</p>
    </div>
</div>

{{-- Decision #6: Compact Secondary Stats --}}
<div class="kpi-secondary-row">
    <div class="kpi-secondary-item">
        <span style="display:inline-block; width: 8px; height: 8px; border-radius:50%; background:#10b981;"></span>
        <strong>Excellent:</strong> {{ $countExcellent }}
    </div>
    <div class="kpi-secondary-item">
        <span style="display:inline-block; width: 8px; height: 8px; border-radius:50%; background:#22c55e;"></span>
        <strong>On Track:</strong> {{ $countOnTrack }}
    </div>
    <div class="kpi-secondary-item">
        <span style="display:inline-block; width: 8px; height: 8px; border-radius:50%; background:#9ca3af;"></span>
        <strong>Not Reported:</strong> {{ $countNotReported }}
    </div>
</div>

{{-- GRID ROW 1: Exception Widgets --}}
<div class="control-tower-grid">
    {{-- WIDGET 1: Sasaran Mutu Butuh Perhatian (Decision #5) --}}
    <div class="card card-section card-inner" style="padding: 16px;">
        <h2 style="font-size: 14px; color: var(--error); margin: 0 0 12px 0; border-bottom: 1px solid var(--outline-variant); padding-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: var(--error);">error</span>
            Sasaran Mutu Butuh Perhatian (Top 10 Worst)
        </h2>
        <div style="overflow-x: auto;">
            <table class="table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Dept</th>
                        <th>Target & KPI</th>
                        <th style="text-align: right;">Capaian %</th>
                        <th>Status</th>
                        <th>PIC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attentionList as $obj)
                        <tr>
                            <td>
                                <a href="{{ route('quality-objectives.objectives.show', $obj->id) }}">
                                    <strong>{{ $obj->code }}</strong>
                                </a>
                            </td>
                            <td><span class="badge" style="background:#ededf9; color:#434655; font-size:10px;">{{ $obj->department->code }}</span></td>
                            <td>
                                <div style="font-weight:600;">{{ $obj->process_name }}</div>
                                <div style="font-size:10px; color: var(--muted);">Target: {{ $obj->target_value }} {{ $obj->unit }}</div>
                            </td>
                            <td style="text-align: right;">
                                <strong>{{ number_format($obj->current_achievement_pct, 1) }}%</strong>
                            </td>
                            <td>
                                @include('quality-objectives._partials._status_badge', ['status' => $obj->current_achievement_status])
                            </td>
                            <td>{{ $obj->pic->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #16a34a; font-weight: 600; padding: 20px;">
                                <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 4px;">check_circle</span>
                                Semua sasaran mutu berjalan On Track!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- WIDGET 2: Action Plan Due Soon (Decision #3) --}}
    <div class="card card-section card-inner" style="padding: 16px;">
        <h2 style="font-size: 14px; color: var(--accent); margin: 0 0 12px 0; border-bottom: 1px solid var(--outline-variant); padding-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: var(--accent);">schedule</span>
            Action Plan Due Soon (Overdue & <= 10 Hari)
        </h2>
        <div style="overflow-x: auto;">
            <table class="table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>Program Kerja</th>
                        <th>Dept</th>
                        <th>PIC</th>
                        <th>Target Selesai</th>
                        <th style="text-align: right;">Sisa Hari</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dueSoonActionPlans as $plan)
                        <tr class="{{ $plan->remaining_days < 0 ? 'row-overdue' : 'row-due-soon' }}">
                            <td>
                                <a href="{{ route('quality-objectives.objectives.show', $plan->objective_id) }}">
                                    <strong>{{ $plan->program_name }}</strong>
                                </a>
                            </td>
                            <td><span class="badge" style="background:#ededf9; color:#434655; font-size:10px;">{{ $plan->objective->department->code }}</span></td>
                            <td>{{ $plan->pic->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($plan->target_date)->format('d/m/Y') }}</td>
                            <td style="text-align: right;">
                                @if($plan->remaining_days < 0)
                                    <span style="color: var(--error); font-weight: 700;">Overdue ({{ abs($plan->remaining_days) }} hari)</span>
                                @elseif($plan->remaining_days == 0)
                                    <span style="color: var(--accent); font-weight: 700;">Hari ini</span>
                                @else
                                    <strong>{{ $plan->remaining_days }} hari lagi</strong>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--muted); padding: 20px;">
                                Tidak ada program kerja yang overdue atau mendekati batas waktu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- GRID ROW 2: Discipline & Rankings --}}
<div class="control-tower-grid">
    {{-- WIDGET 3: Belum Melapor (Decision #1 & #2) --}}
    <div class="card card-section card-inner" style="padding: 16px;">
        <h2 style="font-size: 14px; color: #4b5563; margin: 0 0 12px 0; border-bottom: 1px solid var(--outline-variant); padding-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #4b5563;">pending_actions</span>
            Belum Melapor (Tunggakan Bulan Ini)
        </h2>
        <div style="overflow-x: auto;">
            <table class="table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>Departemen</th>
                        <th>Sasaran Mutu</th>
                        <th>Estimasi Periode</th>
                        <th>Batas Grace Period</th>
                        <th style="text-align: right;">Keterlambatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($missingReports as $report)
                        <tr>
                            <td><span class="badge" style="background:#ededf9; color:#434655; font-size:10px;">{{ $report['objective']->department->code }}</span></td>
                            <td>
                                <a href="{{ route('quality-objectives.objectives.show', $report['objective']->id) }}">
                                    <strong>{{ $report['objective']->code }} - {{ $report['objective']->process_name }}</strong>
                                </a>
                            </td>
                            <td><strong>{{ $report['expected_period'] }}</strong></td>
                            <td>{{ $report['missing_since'] }}</td>
                            <td style="text-align: right; color: var(--error); font-weight: 700;">
                                +{{ $report['delay_days'] }} hari
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #16a34a; font-weight: 600; padding: 20px;">
                                <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 4px;">verified</span>
                                Seluruh pelaporan bulan ini tertib & lengkap!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- WIDGET 4: Department Rankings (Decision #7) --}}
    <div class="card card-section card-inner" style="padding: 16px;">
        <h2 style="font-size: 14px; color: var(--primary); margin: 0 0 12px 0; border-bottom: 1px solid var(--outline-variant); padding-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: var(--primary);">equalizer</span>
            Peringkat Kinerja Departemen
        </h2>
        <div style="overflow-x: auto;">
            <table class="table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th style="width: 50px;">Rank</th>
                        <th>Kode</th>
                        <th>Nama Departemen</th>
                        <th>Target</th>
                        <th style="text-align: right;">Rerata Capaian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deptRankings as $index => $rank)
                        <tr>
                            <td><strong>#{{ $index + 1 }}</strong></td>
                            <td><span class="badge" style="background:#ededf9; color:#434655; font-size:10px;">{{ $rank['code'] }}</span></td>
                            <td>{{ $rank['name'] }}</td>
                            <td>{{ $rank['total_objectives'] }} target</td>
                            <td style="text-align: right;">
                                <strong>
                                    @if($rank['avg_achievement'] !== null)
                                        {{ number_format($rank['avg_achievement'], 1) }}%
                                    @else
                                        <span style="color: var(--muted); font-style: italic;">N/A</span>
                                    @endif
                                </strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--muted); padding: 20px;">
                                Tidak ada data peringkat departemen.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
