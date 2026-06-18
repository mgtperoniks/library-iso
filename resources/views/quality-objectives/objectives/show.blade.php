@extends('layouts.iso')

@section('title', 'Detail Sasaran Mutu ' . $objective->code)

@section('content')
<div class="site-header" style="margin-bottom: 24px;">
    <div class="brand">
        <div class="brand-text">
            <h1>Sasaran Mutu: {{ $objective->code }}</h1>
            <p class="sub">Detail Target, Kinerja, dan Riwayat Workflow</p>
        </div>
    </div>
    <div>
        <a href="{{ route('quality-objectives.objectives.index') }}" class="btn btn-secondary">
            <span class="material-symbols-outlined">arrow_back</span>
            Kembali ke Daftar
        </a>
    </div>
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

<div class="dashboard-grid">
    {{-- LEFT: Detail Information --}}
    <div>
        <div class="card card-section card-inner" style="margin-bottom: 20px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--outline-variant); padding-bottom: 12px; margin-bottom: 16px;">
                <h2 style="margin: 0; font-size: 18px; color: var(--primary);">Identitas Sasaran Mutu</h2>
                <div>
                    @include('quality-objectives._partials._status_badge', ['status' => $objective->status])
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr style="border-bottom: 1px solid #f3f3fe;">
                    <td style="padding: 10px 0; font-weight: 600; width: 30%; color: var(--muted);">Periode Tahun</td>
                    <td style="padding: 10px 0;">Tahun {{ $objective->period->year }} ({{ $objective->period->title }})</td>
                </tr>
                <tr style="border-bottom: 1px solid #f3f3fe;">
                    <td style="padding: 10px 0; font-weight: 600; color: var(--muted);">Departemen</td>
                    <td style="padding: 10px 0;">[{{ $objective->department->code }}] {{ $objective->department->name }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f3f3fe;">
                    <td style="padding: 10px 0; font-weight: 600; color: var(--muted);">Nama Proses / Layanan</td>
                    <td style="padding: 10px 0; font-weight: 600; color: var(--on-surface);">{{ $objective->process_name }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f3f3fe;">
                    <td style="padding: 10px 0; font-weight: 600; color: var(--muted);">Pernyataan Sasaran Mutu</td>
                    <td style="padding: 10px 0; white-space: pre-wrap; line-height: 1.5;">{{ $objective->objective_statement }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f3f3fe;">
                    <td style="padding: 10px 0; font-weight: 600; color: var(--muted);">Indikator Kinerja (KPI)</td>
                    <td style="padding: 10px 0; font-weight: 600; color: var(--accent);">{{ $objective->kpi_indicator }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f3f3fe;">
                    <td style="padding: 10px 0; font-weight: 600; color: var(--muted);">Target Nilai & Satuan</td>
                    <td style="padding: 10px 0;">{{ $objective->target_value }} {{ $objective->unit }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f3f3fe;">
                    <td style="padding: 10px 0; font-weight: 600; color: var(--muted);">Polaritas Target</td>
                    <td style="padding: 10px 0;">
                        @if($objective->target_polarity === 'gte')
                            <span style="font-weight: 600; color: #16a34a;">&ge; (Greater Than or Equal / Lebih Besar dari)</span>
                        @else
                            <span style="font-weight: 600; color: #ba1a1a;">&le; (Less Than or Equal / Lebih Kecil dari)</span>
                        @endif
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #f3f3fe;">
                    <td style="padding: 10px 0; font-weight: 600; color: var(--muted);">Frekuensi Pemantauan</td>
                    <td style="padding: 10px 0; text-transform: capitalize;">{{ $objective->monitoring_frequency }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f3f3fe;">
                    <td style="padding: 10px 0; font-weight: 600; color: var(--muted);">Metode Pengukuran</td>
                    <td style="padding: 10px 0;">{{ $objective->measurement_method ?: '-' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f3f3fe;">
                    <td style="padding: 10px 0; font-weight: 600; color: var(--muted);">PIC Penanggung Jawab</td>
                    <td style="padding: 10px 0;">{{ $objective->pic ? $objective->pic->name : 'Belum ditugaskan' }}</td>
                </tr>
                @if($objective->renewalOf)
                    <tr style="border-bottom: 1px solid #f3f3fe;">
                        <td style="padding: 10px 0; font-weight: 600; color: var(--muted);">Pembaruan Dari</td>
                        <td style="padding: 10px 0;">
                            <a href="{{ route('quality-objectives.objectives.show', $objective->renewalOf->id) }}">
                                {{ $objective->renewalOf->code }} (Tahun {{ $objective->renewalOf->period->year }})
                            </a>
                        </td>
                    </tr>
                @endif
            </table>
        </div>

        {{-- ============================================================
             ACTION PLANS SECTION (FR/MR/20)
             ============================================================ --}}
        <div class="card card-section card-inner" style="margin-bottom: 20px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--outline-variant); padding-bottom: 12px; margin-bottom: 16px;">
                <h2 style="margin: 0; font-size: 16px; color: var(--primary);">Program Kerja & Rencana Tindakan (Action Plans)</h2>
                @if($objective->period->status !== 'closed' && $objective->period->status !== 'archived')
                    <a href="{{ route('quality-objectives.action-plans.create', ['objective_id' => $objective->id]) }}" class="btn btn-sm btn-primary">
                        <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">add</span>
                        Tambah Program
                    </a>
                @endif
            </div>

            <table class="table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th style="width: 50px;">Langkah</th>
                        <th>Rencana Kegiatan</th>
                        <th>PIC</th>
                        <th>Target Selesai</th>
                        <th>Kemajuan</th>
                        <th>Status</th>
                        @if($objective->period->status !== 'closed' && $objective->period->status !== 'archived')
                            <th style="text-align: right; width: 120px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($actionPlans as $plan)
                        <tr>
                            <td><strong>#{{ $plan->sequence }}</strong></td>
                            <td>
                                <strong>{{ $plan->program_name }}</strong>
                                @if($plan->description)
                                    <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">{{ $plan->description }}</div>
                                @endif
                            </td>
                            <td>{{ $plan->pic ? $plan->pic->name : '-' }}</td>
                            <td>{{ $plan->target_date ? $plan->target_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="flex: 1; height: 6px; background: #ededf9; border-radius: 3px; overflow: hidden; min-width: 50px;">
                                        <div style="width: {{ $plan->progress_pct }}%; height: 100%; background: var(--accent);"></div>
                                    </div>
                                    <span>{{ $plan->progress_pct }}%</span>
                                </div>
                            </td>
                            <td>
                                @if($plan->status === 'completed')
                                    <span class="badge badge-approved">Selesai</span>
                                @elseif($plan->status === 'in_progress')
                                    <span class="badge badge-submitted">In Progress</span>
                                @elseif($plan->status === 'overdue')
                                    <span class="badge badge-rejected">Overdue</span>
                                @elseif($plan->status === 'cancelled')
                                    <span class="badge badge-muted">Batal</span>
                                @else
                                    <span class="badge badge-draft">Open</span>
                                @endif
                            </td>
                            @if($objective->period->status !== 'closed' && $objective->period->status !== 'archived')
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="{{ route('quality-objectives.action-plans.edit', $plan->id) }}" class="btn btn-sm btn-outline" style="margin-right: 6px; padding: 4px 8px;">
                                        Edit
                                    </a>
                                    <form action="{{ route('quality-objectives.action-plans.destroy', $plan->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 8px;">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--muted); padding: 16px;">
                                Belum ada rencana program kerja yang ditambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============================================================
             MONITORING RECORDS SECTION (FR/MR/25)
             ============================================================ --}}
        <div class="card card-section card-inner" style="margin-bottom: 20px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--outline-variant); padding-bottom: 12px; margin-bottom: 16px;">
                <h2 style="margin: 0; font-size: 16px; color: var(--primary);">Data Pemantauan & Pencapaian Berkala</h2>
                @if($objective->period->status !== 'closed' && $objective->period->status !== 'archived')
                    <a href="{{ route('quality-objectives.monitorings.create', ['objective_id' => $objective->id]) }}" class="btn btn-sm btn-primary">
                        <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">add</span>
                        Tambah Pemantauan
                    </a>
                @endif
            </div>

            <table class="table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Target</th>
                        <th>Realisasi</th>
                        <th>Capaian %</th>
                        <th>Status</th>
                        <th>Bukti / Dokumen</th>
                        <th style="text-align: right; width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monitorings as $mon)
                        <tr>
                            <td><strong>{{ $mon->period_label }}</strong></td>
                            <td>{{ $mon->target_snapshot }} {{ $objective->unit }}</td>
                            <td>
                                @if($mon->realization_value !== null)
                                    {{ $mon->realization_value }} {{ $objective->unit }}
                                @else
                                    <span style="color: var(--muted); font-style: italic;">Belum lapor</span>
                                @endif
                            </td>
                            <td>
                                @if($mon->achievement_pct !== null)
                                    <strong>{{ number_format($mon->achievement_pct, 1) }}%</strong>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @include('quality-objectives._partials._status_badge', ['status' => $mon->achievement_status])
                            </td>
                            <td>
                                @if($mon->evidences->count() > 0)
                                    @foreach($mon->evidences as $ev)
                                        <a href="{{ asset($ev->file_path) }}" target="_blank" title="{{ $ev->notes }}" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-outlined" style="font-size: 16px;">attachment</span>
                                            File
                                        </a>
                                    @endforeach
                                @else
                                    <span style="color: var(--muted); font-style: italic;">Tidak ada</span>
                                @endif
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                @if($mon->is_locked)
                                    <span class="small-muted" style="display: inline-flex; align-items: center; gap: 4px; color: #16a34a; font-weight: 600;">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">lock</span>
                                        Terkunci
                                    </span>
                                @else
                                    @if($objective->period->status !== 'closed' && $objective->period->status !== 'archived')
                                        <a href="{{ route('quality-objectives.monitorings.edit', $mon->id) }}" class="btn btn-sm btn-outline" style="margin-right: 4px; padding: 4px 8px;">
                                            Edit
                                        </a>
                                        @if(auth()->user()->hasAnyRole(['admin', 'mr']))
                                            <form action="{{ route('quality-objectives.monitorings.lock', $mon->id) }}" method="POST" style="display: inline-block; margin-right: 4px;" onsubmit="return confirm('Apakah Anda yakin ingin mengunci data ini? Data yang telah dikunci tidak dapat diedit/dihapus kembali.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" style="padding: 4px 8px; font-size: 11px;">Kunci</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('quality-objectives.monitorings.destroy', $mon->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pemantauan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 8px;">Hapus</button>
                                        </form>
                                    @else
                                        <span class="small-muted">Terkunci (Periode Closed)</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--muted); padding: 16px;">
                                Belum ada catatan pemantauan berkala.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Log Approval / Riwayat Workflow --}}
        <div class="card card-section card-inner" style="padding: 20px;">
            <h2 style="font-size: 16px; color: var(--primary); margin-bottom: 12px; border-bottom: 1px solid var(--outline-variant); padding-bottom: 8px;">
                Riwayat Persetujuan & Workflow
            </h2>
            <table class="table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>Petugas</th>
                        <th>Peran</th>
                        <th>Aksi</th>
                        <th>Tahap</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($objective->approvals as $app)
                        <tr>
                            <td><strong>{{ $app->user->name }}</strong></td>
                            <td style="text-transform: uppercase; font-size: 11px;">{{ $app->role }}</td>
                            <td>
                                <span class="badge" style="background:#ededf9; color:#434655;">{{ $app->action }}</span>
                            </td>
                            <td>{{ $app->stage }}</td>
                            <td>{{ $app->note }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--muted); padding: 16px;">
                                Belum ada riwayat aktivitas workflow.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- RIGHT: Sidebar Actions & Kinerja --}}
    <div>
        {{-- Card Kinerja Real-Time --}}
        <div class="card card-section card-inner" style="margin-bottom: 20px; padding: 20px; border-left: 4px solid var(--accent);">
            <h2 style="font-size: 16px; margin: 0 0 12px 0;">Kinerja Saat Ini</h2>
            <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 16px;">
                <div style="font-size: 36px; font-weight: 700; color: var(--on-surface);">
                    @if($objective->current_achievement_pct !== null)
                        {{ number_format($objective->current_achievement_pct, 1) }}%
                    @else
                        N/A
                    @endif
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--muted); margin-bottom: 4px;">Status Kinerja</div>
                    @include('quality-objectives._partials._status_badge', ['status' => $objective->current_achievement_status])
                </div>
            </div>

            <div style="border-top: 1px solid #f3f3fe; padding-top: 10px; font-size: 13px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: var(--muted);">Rerata Kinerja (Overall)</span>
                    <strong>{{ $objective->overall_achievement_pct !== null ? number_format($objective->overall_achievement_pct, 1) . '%' : 'N/A' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: var(--muted);">Tren Performa</span>
                    @include('quality-objectives._partials._trend_icon', ['trend' => $objective->trend_direction])
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--muted);">Kepatuhan Laporan</span>
                    <strong>{{ number_format($objective->reporting_compliance_pct, 1) }}%</strong>
                </div>
            </div>
        </div>

        {{-- Card Tindakan Operasional (Workflow Buttons) --}}
        <div class="card card-section card-inner" style="margin-bottom: 20px; padding: 20px;">
            <h2 style="font-size: 15px; margin: 0 0 12px 0; color: var(--primary);">Tindakan Alur Kerja</h2>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @can('submit', $objective)
                    <form action="{{ route('quality-objectives.objectives.submit', $objective->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan sasaran mutu ini untuk persetujuan?');">
                        @csrf
                        <div style="margin-bottom: 8px;">
                            <input type="text" name="note" class="form-input" placeholder="Tulis catatan pengajuan (opsional)" style="padding: 8px;">
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <span class="material-symbols-outlined">send</span>
                            Ajukan Persetujuan
                        </button>
                    </form>
                @endcan

                @can('update', $objective)
                    <a href="{{ route('quality-objectives.objectives.edit', $objective->id) }}" class="btn btn-secondary" style="width: 100%;">
                        <span class="material-symbols-outlined">edit</span>
                        Edit Sasaran Mutu
                    </a>
                @endcan

                @can('delete', $objective)
                    <form action="{{ route('quality-objectives.objectives.destroy', $objective->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus target ini? Tindakan ini tidak dapat dibatalkan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="width: 100%;">
                            <span class="material-symbols-outlined">delete</span>
                            Hapus Draft
                        </button>
                    </form>
                @endcan

                @if($objective->status === 'active')
                    {{-- Perpanjangan (Renew / Clone) --}}
                    <div style="border-top: 1px solid var(--outline-variant); padding-top: 12px; margin-top: 6px;">
                        <p style="font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 8px;">Perpanjang Sasaran Mutu</p>
                        <a href="{{ route('quality-objectives.objectives.renew-form', $objective->id) }}" class="btn btn-success" style="width: 100%;">
                            <span class="material-symbols-outlined">autorenew</span>
                            Pilih Periode & Perpanjang
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
