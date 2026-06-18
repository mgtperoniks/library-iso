@extends('layouts.iso')

@section('title', 'Perpanjang Sasaran Mutu ' . $objective->code)

@section('content')
<div class="site-header" style="margin-bottom: 24px;">
    <div class="brand">
        <div class="brand-text">
            <h1>Perpanjang Sasaran Mutu: {{ $objective->code }}</h1>
            <p class="sub">Pilih Periode Target dan Konfirmasi Penggandaan Target (Annual Renewal)</p>
        </div>
    </div>
    <div>
        <a href="{{ route('quality-objectives.objectives.show', $objective->id) }}" class="btn btn-secondary">
            Batal
        </a>
    </div>
</div>

<div class="card card-section card-inner" style="padding: 24px; max-width: 800px; margin: 0 auto;">
    <h2 style="font-size: 18px; color: var(--primary); margin-bottom: 16px; border-bottom: 1px solid var(--outline-variant); padding-bottom: 8px;">
        Preview Pembaruan Sasaran Mutu
    </h2>

    <p style="font-size: 14px; line-height: 1.5; color: var(--muted); margin-bottom: 20px;">
        Pembaruan (renewal) tahunan dilakukan dengan menduplikasi seluruh informasi sasaran mutu ini ke periode target yang dipilih sebagai status <strong>Draft</strong>. Data historis realisasi tahun lalu tidak akan diduplikasi.
    </p>

    <form action="{{ route('quality-objectives.objectives.renew', $objective->id) }}" method="POST">
        @csrf

        {{-- Step 1: Pilih Periode Baru --}}
        <div class="form-row" style="margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: var(--on-surface);">
                Pilih Periode Baru (Target Period) <span style="color: var(--error);">*</span>
            </label>
            <select name="target_period_id" class="form-input" style="padding: 10px;" required>
                <option value="">-- Pilih Periode Target --</option>
                @foreach($periods as $p)
                    <option value="{{ $p->id }}">Tahun {{ $p->year }} ({{ $p->title }})</option>
                @endforeach
            </select>
        </div>

        {{-- Preview Cloning Fields --}}
        <div style="background: var(--surface-container-low); border: 1px solid var(--outline-variant); border-radius: 8px; padding: 16px; margin-bottom: 24px;">
            <h3 style="font-size: 14px; margin-top: 0; margin-bottom: 12px; color: var(--primary);">Informasi yang Akan Diduplikasi:</h3>
            
            <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 8px 0; font-weight: 600; width: 35%; color: var(--muted);">Departemen</td>
                    <td style="padding: 8px 0;">{{ $objective->department->name }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 8px 0; font-weight: 600; color: var(--muted);">Nama Proses</td>
                    <td style="padding: 8px 0; font-weight: 600;">{{ $objective->process_name }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 8px 0; font-weight: 600; color: var(--muted);">Pernyataan Target</td>
                    <td style="padding: 8px 0;">{{ $objective->objective_statement }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 8px 0; font-weight: 600; color: var(--muted);">KPI Indicator</td>
                    <td style="padding: 8px 0; color: var(--accent); font-weight: 600;">{{ $objective->kpi_indicator }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 8px 0; font-weight: 600; color: var(--muted);">Target Nilai</td>
                    <td style="padding: 8px 0;">{{ $objective->target_value }} {{ $objective->unit }} (Polaritas: {{ strtoupper($objective->target_polarity) }})</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 8px 0; font-weight: 600; color: var(--muted);">Frekuensi Pantau</td>
                    <td style="padding: 8px 0; text-transform: capitalize;">{{ $objective->monitoring_frequency }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: 600; color: var(--muted);">PIC Penanggung Jawab</td>
                    <td style="padding: 8px 0;">{{ $objective->pic ? $objective->pic->name : '-' }}</td>
                </tr>
            </table>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('quality-objectives.objectives.show', $objective->id) }}" class="btn btn-secondary">
                Batal
            </a>
            <button type="submit" class="btn btn-success">
                <span class="material-symbols-outlined">check_circle</span>
                Konfirmasi Perpanjangan (Renew)
            </button>
        </div>
    </form>
</div>
@endsection
